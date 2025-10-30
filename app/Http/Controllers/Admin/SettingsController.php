<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataAccessModels\UsuarioModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    protected $usuarioModel;

    /**
     * Constructor - Inyección de PDO
     */
    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Mostrar la página de configuración
     */
    public function index()
    {
        return view('admin.settings');
    }

    /**
     * Actualizar información del perfil
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Validación
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'career' => ['nullable', 'string', 'max:255'],
            'semester' => ['nullable', 'integer', 'min:1', 'max:10'],
        ], [
            'name.required' => 'El nombre es obligatorio',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Debe ser un correo electrónico válido',
            'email.unique' => 'Este correo electrónico ya está en uso',
            'phone.max' => 'El teléfono no puede exceder 20 caracteres',
            'semester.min' => 'El semestre debe ser mayor a 0',
            'semester.max' => 'El semestre no puede ser mayor a 10',
        ]);

        try {
            // Actualizar usuario usando PDO con sp_actualizar_usuario_completo
            $result = $this->usuarioModel->actualizarUsuarioCompleto(
                $user->id,
                $validated['name'],
                $validated['email'],
                $user->role_id, // Mantener el rol actual
                $user->student_code, // Mantener el código de estudiante
                $validated['career'] ?? null,
                $validated['semester'] ?? null,
                $validated['phone'] ?? null
            );

            if ($result) {
                return redirect()
                    ->route('admin.settings.index')
                    ->with('success', '¡Perfil actualizado exitosamente!');
            } else {
                throw new \Exception('No se pudo actualizar el perfil');
            }

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al actualizar el perfil. Intenta nuevamente.')
                ->withInput();
        }
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Validación
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required', 
                'string',
                'min:8',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ], [
            'current_password.required' => 'Debes ingresar tu contraseña actual',
            'new_password.required' => 'La nueva contraseña es obligatoria',
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres',
            'new_password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        // Verificar contraseña actual
        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()
                ->back()
                ->withErrors(['current_password' => 'La contraseña actual es incorrecta'])
                ->withInput();
        }

        // Verificar que la nueva contraseña sea diferente
        if (Hash::check($validated['new_password'], $user->password)) {
            return redirect()
                ->back()
                ->withErrors(['new_password' => 'La nueva contraseña debe ser diferente a la actual'])
                ->withInput();
        }

        try {
            // Actualizar contraseña usando PDO
            $result = $this->usuarioModel->actualizarPassword(
                $user->id, 
                Hash::make($validated['new_password'])
            );

            if ($result) {
                return redirect()
                    ->route('admin.settings.index')
                    ->with('success', '¡Contraseña actualizada exitosamente! Por seguridad, considera cerrar sesión en otros dispositivos.');
            } else {
                throw new \Exception('No se pudo actualizar la contraseña');
            }

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al cambiar la contraseña. Intenta nuevamente.');
        }
    }
}