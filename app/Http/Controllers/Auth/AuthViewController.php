<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\Empleador;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthViewController extends Controller
{
    /**
     * RN-01 / RN-02: según el rol del usuario autenticado,
     * se redirige al panel correspondiente.
     */
    private const DASHBOARD_BY_ROLE = [
        'candidato' => 'candidate.dashboard',
        'empleador' => 'employer.dashboard',
        'admin'     => 'admin.dashboard',
    ];

    public function showLogin(): View
    {
        // Siempre muestra el formulario: con sesión activa funciona
        // como "cambiar de cuenta" (el POST re-autentica).
        return view('auth.login');
    }

    public function showRoleSelection(): View
    {
        // Siempre muestra la selección: desde aquí vuelve el botón
        // "Volver" de los logines, incluso con sesión activa.
        return view('auth.role');
    }

    public function showLoginCandidate(): View
    {
        return view('auth.login-candidate');
    }

    public function showLoginEmployer(): View
    {
        return view('auth.login-employer');
    }

    public function showRegisterCandidate(): View
    {
        return view('auth.register-candidate');
    }

    public function showRegisterEmployer(): View
    {
        return view('auth.register-employer');
    }

    public function showDashboardDemo(): View
    {
        return view('auth.dashboard-demo');
    }

    /**
     * Autenticación: valida credenciales y redirige según el rol (RN-01, RN-02).
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
            // El portal de origen del formulario (si viene de un login por rol)
            'portal'   => 'nullable|in:candidato,empleador',
        ], [
            'email.required'    => 'El correo electrónico es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $remember = $request->boolean('remember');
        $portal   = $request->input('portal');

        if (! Auth::attempt($request->only('email', 'password'), $remember)) {
            return back()
                ->withErrors(['email' => 'Credenciales incorrectas. Verificá tu correo y tu contraseña.'])
                ->withInput($request->only('email'));
        }

        $user = Auth::user();

        // Solo las cuentas activas pueden acceder
        if ($user->estado !== 'activo') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Tu cuenta está desactivada. Contactá al administrador.'])
                ->withInput($request->only('email'));
        }

        // RN-02: cada portal solo acepta cuentas de su tipo
        // (el login de empresa no admite candidatos y viceversa)
        if ($portal !== null && $user->rol !== $portal) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $mensaje = $portal === 'empleador'
                ? 'Esta cuenta no corresponde al portal de empresas. Ingresá por el portal de candidatos.'
                : 'Esta cuenta no corresponde al portal de candidatos. Ingresá por el portal de empresas.';

            return back()
                ->withErrors(['email' => $mensaje])
                ->withInput($request->only('email'));
        }

        // Regenerar el ID de sesión (protección contra session fixation)
        $request->session()->regenerate();

        // Registrar el último acceso
        $user->forceFill(['ultimo_acceso' => now()])->save();

        $dashboard = self::DASHBOARD_BY_ROLE[$user->rol] ?? 'admin.dashboard';

        return redirect()->intended(route($dashboard));
    }

    /**
     * Cierre de sesión: destruye la sesión y regenera el token CSRF.
     */
    /**
     * Registro de candidato: crea usuario + perfil y entra automáticamente (RN-01).
     */
    public function registerCandidate(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'    => 'required|string|max:100',
            'email'     => 'required|email|max:100|unique:usuario,email',
            'password'  => 'required|string|min:8|confirmed',
        ], [
            'nombre.required'    => 'El nombre completo es obligatorio.',
            'email.required'     => 'El correo electrónico es obligatorio.',
            'email.unique'       => 'Ya existe una cuenta con ese correo electrónico.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario = DB::transaction(function () use ($request) {
            $usuario = Usuario::create([
                'nombre'         => $request->nombre,
                'email'          => $request->email,
                'password'       => Hash::make($request->password),
                'rol'            => 'candidato',
                'estado'         => 'activo',
                'fecha_registro' => now(),
            ]);

            Candidato::create([
                'id_usuario'     => $usuario->id_usuario,
                'estado_laboral' => 'Buscando oportunidades',
            ]);

            return $usuario;
        });

        // Autologin + redirigir al panel correspondiente
        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('candidate.dashboard');
    }

    /**
     * Registro de empleador: crea usuario + empresa y entra automáticamente (RN-01).
     */
    public function registerEmployer(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre_empresa' => 'required|string|max:100',
            'ruc'            => 'required|string|max:20|unique:empleador,ruc',
            'email'          => 'required|email|max:100|unique:usuario,email',
            'password'       => 'required|string|min:8|confirmed',
        ], [
            'nombre_empresa.required' => 'El nombre de la empresa es obligatorio.',
            'ruc.required'            => 'El RUC es obligatorio.',
            'ruc.unique'              => 'Ya existe una empresa registrada con ese RUC.',
            'email.required'          => 'El correo corporativo es obligatorio.',
            'email.unique'            => 'Ya existe una cuenta con ese correo electrónico.',
            'password.required'       => 'La contraseña es obligatoria.',
            'password.min'            => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed'      => 'Las contraseñas no coinciden.',
        ]);

        $usuario = DB::transaction(function () use ($request) {
            $usuario = Usuario::create([
                'nombre'         => $request->nombre_empresa,
                'email'          => $request->email,
                'password'       => Hash::make($request->password),
                'rol'            => 'empleador',
                'estado'         => 'activo',
                'fecha_registro' => now(),
            ]);

            Empleador::create([
                'id_usuario'     => $usuario->id_usuario,
                'nombre_empresa' => $request->nombre_empresa,
                'ruc'            => $request->ruc,
                // La dirección geolocalizada se asociará al gestionar sedes/mapa
            ]);

            return $usuario;
        });

        // Autologin + redirigir al panel correspondiente
        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('employer.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
