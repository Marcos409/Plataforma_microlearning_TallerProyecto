<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
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
            // Preparar datos para actualizar
            $dataToUpdate = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'career' => $validated['career'] ?? null,
                'semester' => $validated['semester'] ?? null,
            ];

            // Actualizar usuario (usando el mismo método que AdminUserController)
            $user->update($dataToUpdate);

            return redirect()
                ->route('admin.settings.index')
                ->with('success', '¡Perfil actualizado exitosamente!');

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
            // Actualizar contraseña
            $user->update([
                'password' => Hash::make($validated['new_password']),
            ]);

            return redirect()
                ->route('admin.settings.index')
                ->with('success', '¡Contraseña actualizada exitosamente! Por seguridad, considera cerrar sesión en otros dispositivos.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al cambiar la contraseña. Intenta nuevamente.');
        }
    }
}