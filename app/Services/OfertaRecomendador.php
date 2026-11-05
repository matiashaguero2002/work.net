<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Oferta;
use Carbon\Carbon;

/**
 * Recomendador de ofertas (opción A: scoring explicable en PHP puro).
 *
 * Arma las "señales" del candidato desde la BD (habilidades, Sobre mí,
 * experiencia, formación, idiomas) y puntúa cada oferta activa de 0 a 100.
 *
 * Fórmula (pesos máximos):
 *   40 · habilidades del catálogo (lvl Avanzado pesa más)
 *   25 · keywords de texto ("Sobre mí")
 *   15 · palabras de la experiencia (puesto/tareas)
 *   10 · palabras de la formación (títulos/cursos)
 *    5 · idiomas pedidos que el candidato tiene
 *   +5 · frescura (publicada en los últimos 7 días)
 *
 * Todas las señales se normalizan: minúsculas, sin acentos, stopwords,
 * singular simple y un diccionario de sinónimos (js↔javascript, bd↔sql,
 * laravel→php, etc.) para que los matches no dependan de cómo se escribió.
 */
class OfertaRecomendador
{
    /** Pes máximo de cada componente. */
    private const MAX_HABILIDADES = 40;
    private const MAX_KEYWORDS = 25;
    private const MAX_EXPERIENCIA = 15;
    private const MAX_FORMACION = 10;
    private const MAX_IDIOMAS = 5;
    private const PUNTOS_FRESCURA = 5;

    /** Token → token canónico: distintos modos de referirse a lo mismo. */
    private const SINONIMOS = [
        'js' => 'javascript', 'javascript' => 'javascript', 'typescript' => 'javascript',
        'ts' => 'javascript', 'react' => 'react', 'vue' => 'vue', 'angular' => 'angular',
        'php' => 'php', 'laravel' => 'php', 'codeigniter' => 'php', 'wordpress' => 'php',
        'sql' => 'sql', 'bd' => 'sql', 'db' => 'sql', 'mysql' => 'sql', 'postgres' => 'sql',
        'postgresql' => 'sql', 'sqlite' => 'sql', 'oracle' => 'sql', 'supabase' => 'sql',
        'excel' => 'office', 'word' => 'office', 'powerpoint' => 'office',
        'python' => 'python', 'django' => 'python', 'flask' => 'python',
        'java' => 'java', 'kotlin' => 'java', 'spring' => 'java',
        'figma' => 'diseno', 'adobe' => 'diseno', 'photoshop' => 'diseno',
        'illustrator' => 'diseno', 'ux' => 'diseno', 'ui' => 'diseno',
        'ingles' => 'ingles', 'english' => 'ingles', 'portugues' => 'portugues',
        'guarani' => 'guarani', 'espanol' => 'espanol', 'castellano' => 'espanol',
        'frances' => 'frances', 'aleman' => 'aleman', 'italiano' => 'italiano',
        'ventas' => 'ventas', 'vendedor' => 'ventas', 'marketing' => 'marketing',
        'administrador' => 'admin', 'contabilidad' => 'admin', 'contador' => 'admin',
        'rh' => 'rh', 'inglés' => 'ingles',
    ];

    private const STOPWORDS = [
        'de', 'la', 'el', 'en', 'para', 'con', 'los', 'las', 'por', 'una', 'del', 'se', 'y',
        'o', 'a', 'al', 'es', 'un', 'su', 'que', 'como', 'mas', 'the', 'and', 'of', 'to',
        'buscamos', 'buscan', 'busqueda', 'responsable', 'encargado', 'alta', 'nuestra',
        'empleo', 'vacante', 'empresa', 'cliente', 'clientes', 'anos', 'año', 'te', 'vos',
        'favor', 'pos', 'experiencia', 'perfil', 'necesario', 'deseado', 'jornada',
    ];

    /**
     * Señales del candidato desde la BD:
     * null = sin datos suficientes (cold start, se usa el rango por fecha).
     */
    public static function senales(Candidato $candidato): ?array
    {
        $senales = [
            'skills' => [],      // token => ['label' => mostrado, 'peso' => 1|1.2|1.4]
            'tokensPerfil' => [],
            'tokensExp' => [],
            'tokensForm' => [],
            'idiomas' => [],     // token => nombre mostrado
        ];

        foreach ($candidato->aptitudes()->get() as $aptitud) {
            $token = static::canonico($aptitud->nombre);
            if ($token === '' || isset($senales['skills'][$token])) continue;
            $peso = match ((string) $aptitud->pivot->nivel ?? '') {
                'Avanzado' => 1.4,
                'Intermedio' => 1.2,
                default => 1.0,
            };
            $senales['skills'][$token] = ['label' => $aptitud->nombre, 'peso' => $peso];
        }

        if ($candidato->perfil) {
            $senales['tokensPerfil'] = static::tokens($candidato->perfil);
        }

        foreach ($candidato->experiencias()->get() as $exp) {
            foreach (static::tokens(($exp->puesto ?? '') . ' ' . ($exp->tareas ?? '')) as $tk) {
                $senales['tokensExp'][$tk] = true;
            }
        }

        foreach ($candidato->titulos()->get() as $tit) {
            foreach (static::tokens(($tit->nombre ?? '') . ' ' . ($tit->tipo ?? '')) as $tk) {
                $senales['tokensForm'][$tk] = true;
            }
        }

        foreach ($candidato->idiomas()->get() as $idioma) {
            $token = static::canonico($idioma->nombre);
            if ($token === '' || isset($senales['idiomas'][$token])) continue;
            $senales['idiomas'][$token] = $idioma->nombre;
        }

        $vacio = empty($senales['skills'])
            && empty($senales['tokensPerfil'])
            && empty($senales['tokensExp'])
            && empty($senales['tokensForm'])
            && empty($senales['idiomas']);

        return $vacio ? null : $senales;
    }

    /**
     * Puntúa una oferta (0-100) y devuelve las razones legibles.
     */
    public static function scoreOferta(array $senales, Oferta $oferta): array
    {
        $texto = collect([$oferta->titulo, $oferta->descripcion, $oferta->requisitos, $oferta->beneficios])
            ->filter()
            ->implode(' ');
        $tokens = static::tokens($texto);

        // Mapa de todos los tokens para matching por presencia y sinónimos.
        $tokensSet = array_flip($tokens);
        $razones = [];
        $score = 0.0;
        $frescura = 0;

        // 1) Habilidades (máx 40)
        $matched = [];
        foreach ($senales['skills'] as $token => $info) {
            if (isset($tokensSet[$token])) {
                $score += 12 * $info['peso'];
                $matched[] = $info['label'];
            }
        }
        $score = min($score, self::MAX_HABILIDADES);
        if ($matched) {
            $razones[] = 'Coincide con tus habilidades: ' . implode(', ', array_slice($matched, 0, 4));
        }

        // 2) Keywords del "Sobre mí" (máx 25)
        $matchedPerfil = [];
        foreach ($senales['tokensPerfil'] as $tk) {
            if (isset($tokensSet[$tk])) {
                $matchedPerfil[] = $tk;
            }
        }
        $score += min(count($matchedPerfil) * 4, self::MAX_KEYWORDS);
        if ($matchedPerfil) {
            $razones[] = 'Menciona cosas que contás en tu "Sobre mí": ' . implode(', ', array_slice($matchedPerfil, 0, 4));
        }

        // 3) Experiencia (máx 15)
        $matchedExp = [];
        foreach ($senales['tokensExp'] as $tk => $unused) {
            if (isset($tokensSet[$tk])) $matchedExp[] = $tk;
        }
        $score += min(count($matchedExp) * 6, self::MAX_EXPERIENCIA);
        if ($matchedExp) {
            $razones[] = 'Se relaciona con tu experiencia: ' . implode(', ', array_slice($matchedExp, 0, 3));
        }

        // 4) Formación (máx 10)
        $matchedForm = [];
        foreach ($senales['tokensForm'] as $tk => $unused) {
            if (isset($tokensSet[$tk])) $matchedForm[] = $tk;
        }
        $score += min(count($matchedForm) * 5, self::MAX_FORMACION);
        if ($matchedForm) {
            $razones[] = 'Relacionado con tu formación: ' . implode(', ', array_slice($matchedForm, 0, 3));
        }

        // 5) Idiomas (máx 5)
        $matchedIdiomas = [];
        foreach ($senales['idiomas'] as $token => $label) {
            if (isset($tokensSet[$token])) $matchedIdiomas[] = $label;
        }
        $score += min(count($matchedIdiomas) * 2.5, self::MAX_IDIOMAS);
        if ($matchedIdiomas) {
            $razones[] = 'Pide ' . implode(' / ', $matchedIdiomas) . ' (lo tenés cargado)';
        }

        // 6) Frescura
        if ($oferta->fecha_publicacion && Carbon::parse($oferta->fecha_publicacion) >= now()->subDays(7)) {
            $frescura = self::PUNTOS_FRESCURA;
        }
        $score += $frescura;

        $score = (int) min(100, max(0, round($score)));

        return [
            'score' => $score,
            'razones' => array_slice($razones, 0, 3),
        ];
    }

    /**
     * Texto → array de tokens canónicos (normalización).
     */
    public static function tokens(string $texto): array
    {
        $texto = mb_strtolower($texto ?? '');
        $texto = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $texto);
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n',
        ]);

        $out = [];
        foreach (preg_split('/\s+/', $texto ?: '') as $palabra) {
            if (strlen($palabra) < 3 || ctype_digit($palabra)) continue;
            if (in_array($palabra, self::STOPWORDS, true)) continue;

            // singular simple
            if (strlen($palabra) > 3 && str_ends_with($palabra, 's')) {
                $palabra = substr($palabra, 0, -1);
            }

            $canon = self::SINONIMOS[$palabra] ?? $palabra;
            if ($canon === '') continue;
            $out[$canon] = true; // garantiza uniq's de tokens
        }

        return array_keys($out);
    }

    /** Normaliza un término suelto (para nombres de habilidades). */
    private static function canonico(string $texto): string
    {
        $tk = static::tokens($texto);
        return reset($tk) ?: '';
    }
}
