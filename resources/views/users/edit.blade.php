@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="fas fa-user-edit me-2"></i>Editar Usuario
                    <small class="text-muted">{{ $user->name }}</small>
                </h1>
                <div>
                    <a href="{{ route('users.show', $user) }}" class="btn btn-info me-2">
                        <i class="fas fa-eye me-1"></i>Ver
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>

            <!-- Información del usuario -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="avatar-circle-large">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="col-md-10">
                            <p class="mb-1"><strong>ID:</strong> #{{ $user->id }}</p>
                            <p class="mb-1"><strong>Registrado:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                            <p class="mb-0">
                                <strong>Estado:</strong>
                                @if($user->email_verified_at)
                                    <span class="badge bg-success">Verificado</span>
                                @else
                                    <span class="badge bg-warning">Pendiente verificación</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user me-1"></i>Nombre completo
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required 
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Correo electrónico
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Rol -->
                        <div class="mb-3">
                            <label for="role" class="form-label">
                                <i class="fas fa-user-tag me-1"></i>Rol
                            </label>
                            <select class="form-select @error('role') is-invalid @enderror" 
                                    id="role" 
                                    name="role" 
                                    required>
                                <option value="">Seleccionar rol...</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" 
                                            {{ old('role', $user->role) == $role ? 'selected' : '' }}>
                                        {{ ucfirst($role) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Rol actual: 
                                <span class="badge role-{{ $user->role ?? 'none' }}">
                                    {{ $user->role ? ucfirst($user->role) : 'Sin asignar' }}
                                </span>
                            </div>
                        </div>

                        <!-- Nueva Contraseña (Opcional) -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i>Nueva contraseña
                                <span class="text-muted">(dejar vacío para mantener actual)</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       minlength="8">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Mínimo 8 caracteres (opcional)</div>
                        </div>

                        <!-- Confirmar Nueva Contraseña -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock me-1"></i>Confirmar nueva contraseña
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   minlength="8">
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Actualizar Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Acciones peligrosas -->
            <div class="card mt-3 border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-1"></i>Zona Peligrosa</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Estas acciones son permanentes y no se pueden deshacer.</p>
                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn btn-outline-danger"
                                onclick="return confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')">
                            <i class="fas fa-trash me-1"></i>Eliminar Usuario
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    // Validación de contraseñas en tiempo real
    document.getElementById('password_confirmation').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmation = this.value;
        
        if (password && confirmation && password !== confirmation) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Limpiar confirmación cuando se cambia la contraseña
    document.getElementById('password').addEventListener('input', function() {
        const confirmation = document.getElementById('password_confirmation');
        if (!this.value) {
            confirmation.value = '';
            confirmation.classList.remove('is-invalid');
        }
    });
</script>
@endpush

@push('styles')
<style>
    .avatar-circle-large {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: #007bff;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 24px;
        text-transform: uppercase;
    }

    .role-admin { background-color: #dc3545; color: white; }
    .role-teacher { background-color: #28a745; color: white; }
    .role-student { background-color: #007bff; color: white; }
    .role-none { background-color: #6c757d; color: white; }
</style>
@endpush