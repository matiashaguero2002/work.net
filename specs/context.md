# Context Spec — Work.net

## 1. Identificación del sistema

**Nombre:** Work.net  
**Propósito:** Sistema de búsqueda y localización inteligente de empleo en la ciudad de Encarnación, Paraguay.  
**Alcance:** Prueba de Concepto (PoC) centrada inicialmente en oportunidades laborales del área informática/TI.  
**Estado:** Desarrollo en curso, módulos implementados y probados.  
**Rol del documento:** Especificación de contexto para desarrollo asistido por IA. Define stack, arquitectura, actores, restricciones y límites del sistema.

---

## 2. Stack tecnológico

| Capa | Tecnología | Versión / Uso |
|------|------------|---------------|
| Lenguaje backend | PHP | 8.2.12 (entorno real) |
| Framework backend | Laravel | 12.x |
| ORM | Eloquent | Modelo activo, `$timestamps = false`, fechas con `now()` PHP |
| Base de datos | PostgreSQL | `Work_net`, 39 tablas, sin migraciones nuevas, solo seeders idempotentes |
| Motor de plantillas | Blade | Vistas reescritas con datos reales |
| Frontend | HTML5, CSS3, JavaScript | Sin frameworks de UI pesados, Bootstrap 5 vía CDN |
| Estilos | CSS propio (Vite) | `worknet.css`, `worknet-map.css`, `worknet-employer.css`, etc. |
| Mapas | Leaflet | 1.9.4 vía unpkg |
| Cartografía | OpenStreetMap | Tiles `https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png` |
| Geocodificación | Nominatim | `fetch` nativo, limitado a `countrycodes=py` |
| Autenticación | Laravel Auth | Login, registro, roles (`candidato`, `empleador`, `admin`) |
| Gestión de archivos | Laravel Storage | Disco `public`, CVs en `cv/candidato-{id}/` |
| Notificaciones | Tabla propia `notificacion` + polling AJAX | 30s intervalo, badge en navbar |
| Build / Assets | Vite | CSS/JS compilados en `public/build/assets/` |
| Pruebas | cURL scripts (temporales) | Suite de validación funcional, no PHPUnit versionado |
| Control de versiones | Git | Rama `main`, commits directos sin `develop`/`hotfix` |

---

## 3. Arquitectura

**Patrón:** Modelo-Vista-Controlador (MVC) con capa de servicios.

- **Controladores:** `app/Http/Controllers/` organizados por rol (`Candidate`, `Employer`, `Admin`, `Auth`) y transversales (`MapController`).
- **Modelos:** `app/Models/` con Eloquent, relaciones definidas, `$timestamps = false`.
- **Servicios:** `app/Services/` (`MatchPostulante`, `OfertaRecomendador`, `PropuestaEntrevista`) contienen lógica de negocio pura.
- **Vistas:** `resources/views/` con layouts, partials (`wn-*`) y vistas por módulo.
- **Assets:** `resources/css/`, `resources/js/` compilados con Vite.

No hay `FormRequest`; la validación se realiza inline en controladores (`$request->validate()`).

---

## 4. Actores y roles

| Rol | Tabla | Descripción |
|-----|-------|-------------|
| Candidato | `usuario.rol = 'candidato'` + `candidato` | Busca y filtra ofertas, ve mapa, consulta detalle, se postula, guarda ofertas, gestiona perfil y CV, recibe notificaciones, responde propuestas de reagendamiento. |
| Empleador | `usuario.rol = 'empleador'` + `empleador` | Gestiona perfil de empresa, publica/pausa/reactiva/cierra ofertas, ve postulantes, rechaza/actualiza estados, convoca entrevistas, negocia reagendamiento. |
| Administrador | `usuario.rol = 'admin'` | Supervisa usuarios y ofertas (datos mock hardcodeados, sin consultas reales a BD). |

---

## 5. Módulos funcionales implementados

1. **Autenticación** (`auth/`): login, registro diferenciado, gestión de roles, logout.
2. **Mapa público** (`/mapa`): 10 ofertas activas con marcadores en sede del empleador, deep-links, visualizaciones (RF-05).
3. **Candidato** (`/candidato/*`): dashboard con ofertas sugeridas (algoritmo real `OfertaRecomendador`), perfil, postulaciones, guardadas, entrevistas, notificaciones.
4. **Empleador** (`/empleador/*`): dashboard, ofertas CRUD + estados, postulantes, entrevistas (convocar, cancelar, negociar, responder, visto), notificaciones.
5. **Administrador** (`/admin/*`): panel con datos mock hardcodeados (sin consultas reales a BD).

---

## 6. Reglas de negocio clave (RN)

| ID | Descripción | Implementación |
|----|-------------|----------------|
| RN-01 / RN-02 | Login y roles | Middleware `EnsureRole`, redirección a panel propio |
| RN-03 | Registro empresa | `AuthViewController::registerEmployer` |
| RN-04 / RN-05 / RN-12 / RN-13 | Publicar oferta | `OfferController` con validación, geocodificación |
| RN-06 / RN-07 | Buscar y filtrar ofertas | Panel de filtros en mapa (texto, área, modalidad, contrato, salario, distancia) |
| RN-08 | Visualizaciones | Endpoint `POST /mapa/ofertas/{id}/vista`, contador una vez por carga |
| RN-09 | Gestionar ofertas publicadas | Pausar, reactivar, cerrar, duplicar |
| RN-10 | Gestionar postulaciones recibidas | Rechazar, cambiar estado, ver CV |
| RN-15 / RN-16 | Convocar entrevista | `InterviewController::store`, estado `pendiente`, historial + notif tipo 2 |
| RN-19 | Aceptar/Rechazar propuesta de reagendamiento | Servicio `PropuestaEntrevista`, endpoints `responder-propuesta` y `visto` |

---

## 7. Restricciones del sistema

- **Sin paquetes externos de geolocalización:** geocodificación vía `fetch` Nominatim limitado a `countrycodes=py`.
- **Sin tocar la estructura de la BD PostgreSQL `Work_net`:** solo insertar/actualizar filas (seeders idempotentes). No hay nuevas migraciones ni `artisan migrate`.
- **Sin tocar los prototipos `specs/`:** los prototipos HTML se movieron a `resources/views/prototypes/`; `specs/` es para especificaciones técnicas (`*.md`).
- **Sin CSS inline nuevo** excepto patrón existente `style="width:%"`.
- **Todo el texto de UI en español.**
- **No rediseñar el frontend:** solo cablear funcionalidad, mismas clases/estilos.
- **Push a GitHub solo bajo pedido del usuario.**

---

## 8. Base de datos

- Motor: PostgreSQL, host `127.0.0.1`, puerto `5432`, base `Work_net`, usuario `postgres`, contraseña `admin123`.
- Tablas principales: `oferta`, `requisito_oferta`, `beneficio_oferta`, `experiencia`, `titulo`, `postulacion`, `historial_estado`, `entrevista`, `notificacion`, `empleador`, `candidato`, `ubicacion`, `sede`, `area_laboral`, `tipo_requisito`, `tipo_notificacion`, `estado_oferta`, `estado_postulacion`, `modalidad_trabajo`, `tipo_contrato`.
- Catálogos: `estado_oferta` (1=Activa, 2=Pausada, 3=Cerrada), `estado_postulacion` (1..6), `tipo_notificacion` (1=Postulación, 2=Entrevista, 3=Oferta), `tipo_requisito` (1=Habilidad técnica, 2=Formación académica, 3=Experiencia, 4=Otro).
