<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Muestra el formulario para solicitar restablecimiento de contraseña.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Envía un código de restablecimiento al correo ingresado.
     */
    public function sendResetCode(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:usuario,email',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'Ingresá un correo electrónico válido.',
            'email.exists'   => 'No existe una cuenta con ese correo electrónico.',
        ]);

        $email = $request->email;
        $code  = (string) rand(100000, 999999);

        // Guardar/actualizar token de recuperación (15 minutos de expiración)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token'      => hash('sha256', $code),
                'created_at' => now(),
            ]
        );

        // Enviar el código por correo (driver log en desarrollo; cambiar a smtp en producción)
        Mail::raw(
            "Tu código de restablecimiento de contraseña en Work.net es: {$code}\n\n" .
            "Este código expira en 15 minutos. Si no solicitaste este cambio, ignorá este mensaje.",
            function ($message) use ($email) {
                $message->to($email)
                    ->subject('Work.net — Código de restablecimiento de contraseña');
            }
        );

        return back()
            ->with('success', 'Se envió un código de restablecimiento a tu correo electrónico.')
            ->with('email', $email);
    }

    /**
     * Muestra el formulario para ingresar el código y la nueva contraseña.
     */
    public function showResetForm(string $token): View
    {
        $registro = DB::table('password_reset_tokens')
            ->where('token', hash('sha256', $token))
            ->first();

        if (!$registro) {
            abort(404, 'Código de restablecimiento inválido o expirado.');
        }

        if ($registro->created_at && $registro->created_at->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $registro->email)->delete();
            abort(404, 'El código de restablecimiento ha expirado.');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $registro->email,
        ]);
    }

    /**
     * Restablece la contraseña usando el código enviado por correo.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => 'required|email|exists:usuario,email',
            'code'     => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required'    => 'El correo electrónico es obligatorio.',
            'code.required'     => 'El código es obligatorio.',
            'code.size'         => 'El código debe tener 6 dígitos.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed'=> 'Las contraseñas no coinciden.',
        ]);

        $registro = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$registro || !hash_equals($registro->token, hash('sha256', $request->code))) {
            return back()
                ->withErrors(['code' => 'Código inválido.'])
                ->withInput($request->only('email'));
        }

        if ($registro->created_at && $registro->created_at->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()
                ->withErrors(['code' => 'El código ha expirado. Solicitá uno nuevo.'])
                ->withInput($request->only('email'));
        }

        // Actualizar contraseña y eliminar token usado
        DB::transaction(function () use ($request) {
            Usuario::where('email', $request->email)
                ->update(['password' => Hash::make($request->password)]);

            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
        });

        return redirect()->route('login')
            ->with('success', 'Tu contraseña se restableció correctamente. Ya podés iniciar sesión.');
    }
}
