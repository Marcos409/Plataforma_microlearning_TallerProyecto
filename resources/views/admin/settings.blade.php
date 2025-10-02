@extends('layouts.app')

@section('title', 'Configuración de Cuenta')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-cog me-2"></i>Configuración de Cuenta</h1>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver al Dashboard
                </a>
            </div>

            <!-- Alertas -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Información Personal -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>Información Personal
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.settings.update-profile') }}">
                                @csrf
                                @method('PUT')

                                <!-- Nombre -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user me-1"></i>Nombre Completo <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', Auth::user()->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Correo Electrónico <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', Auth::user()->email) }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Asegúrate de tener acceso a este correo
                                    </small>
                                </div>

                                <!-- Teléfono -->
                                <div class="mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Teléfono
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', Auth::user()->phone) }}"
                                           placeholder="+51 999 999 999">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @if(Auth::user()->isStudent())
                                    <!-- Código de Estudiante (solo lectura) -->
                                    <div class="mb-3">
                                        <label for="student_code" class="form-label">
                                            <i class="fas fa-id-card me-1"></i>Código de Estudiante
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="student_code" 
                                               value="{{ Auth::user()->student_code }}" 
                                               readonly>
                                        <small class="text-muted">Este campo no se puede modificar</small>
                                    </div>

                                    <!-- Carrera -->
                                    <div class="mb-3">
                                        <label for="career" class="form-label">
                                            <i class="fas fa-graduation-cap me-1"></i>Carrera
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('career') is-invalid @enderror" 
                                               id="career" 
                                               name="career" 
                                               value="{{ old('career', Auth::user()->career) }}"
                                               placeholder="Ej: Ingeniería de Sistemas">
                                        @error('career')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Semestre -->
                                    <div class="mb-3">
                                        <label for="semester" class="form-label">
                                            <i class="fas fa-book me-1"></i>Semestre
                                        </label>
                                        <select class="form-select @error('semester') is-invalid @enderror" 
                                                id="semester" 
                                                name="semester">
                                            <option value="">Selecciona un semestre</option>
                                            @for($i = 1; $i <= 10; $i++)
                                                <option value="{{ $i }}" 
                                                        {{ old('semester', Auth::user()->semester) == $i ? 'selected' : '' }}>
                                                    {{ $i }}° Semestre
                                                </option>
                                            @endfor
                                        </select>
                                        @error('semester')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                <hr class="my-4">

                                <!-- Información del Rol (solo lectura) -->
                                <div class="alert alert-info mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle fa-2x me-3"></i>
                                        <div>
                                            <strong>Tu rol actual:</strong>
                                            @if(Auth::user()->role)
                                                <span class="badge bg-primary ms-2">{{ Auth::user()->role->name }}</span>
                                            @else
                                                <span class="badge bg-warning ms-2">Sin rol asignado</span>
                                            @endif
                                            <br>
                                            <small class="text-muted">El rol solo puede ser modificado por un administrador</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Guardar Cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Cambiar Contraseña -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-lock me-2"></i>Seguridad
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.settings.update-password') }}">
                                @csrf
                                @method('PUT')

                                <div class="alert alert-warning mb-4">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <strong>Recomendaciones de seguridad:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Mínimo 8 caracteres</li>
                                        <li>Incluye mayúsculas y minúsculas</li>
                                        <li>Usa números y símbolos</li>
                                        <li>No uses información personal</li>
                                    </ul>
                                </div>

                                <!-- Contraseña Actual -->
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">
                                        <i class="fas fa-key me-1"></i>Contraseña Actual <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control @error('current_password') is-invalid @enderror" 
                                               id="current_password" 
                                               name="current_password" 
                                               required
                                               placeholder="Ingresa tu contraseña actual">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye" id="icon-current_password"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Nueva Contraseña -->
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">
                                        <i class="fas fa-lock me-1"></i>Nueva Contraseña <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control @error('new_password') is-invalid @enderror" 
                                               id="new_password" 
                                               name="new_password" 
                                               required
                                               placeholder="Mínimo 8 caracteres">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                onclick="togglePassword('new_password')">
                                            <i class="fas fa-eye" id="icon-new_password"></i>
                                        </button>
                                    </div>
                                    @error('new_password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <!-- Indicador de fortaleza -->
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar" id="password-strength" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted" id="password-feedback"></small>
                                </div>

                                <!-- Confirmar Nueva Contraseña -->
                                <div class="mb-3">
                                    <label for="new_password_confirmation" class="form-label">
                                        <i class="fas fa-lock me-1"></i>Confirmar Nueva Contraseña <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="new_password_confirmation" 
                                               name="new_password_confirmation" 
                                               required
                                               placeholder="Repite la nueva contraseña">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                onclick="togglePassword('new_password_confirmation')">
                                            <i class="fas fa-eye" id="icon-new_password_confirmation"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Debe coincidir con la nueva contraseña</small>
                                </div>

                                <hr class="my-4">

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-key me-2"></i>Cambiar Contraseña
                                    </button>
                                </div>
                            </form>

                            <!-- Última actualización -->
                            <div class="mt-4 text-center">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Última actualización: {{ Auth::user()->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById('icon-' + fieldId);
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Password strength indicator
    document.getElementById('new_password').addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('password-strength');
        const feedback = document.getElementById('password-feedback');
        
        let strength = 0;
        let message = '';
        let color = '';
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 25;
        if (password.match(/[^a-zA-Z0-9]/)) strength += 25;
        
        strengthBar.style.width = strength + '%';
        
        if (strength < 50) {
            color = 'bg-danger';
            message = 'Contraseña débil';
        } else if (strength < 75) {
            color = 'bg-warning';
            message = 'Contraseña media';
        } else if (strength < 100) {
            color = 'bg-info';
            message = 'Contraseña buena';
        } else {
            color = 'bg-success';
            message = 'Contraseña fuerte';
        }
        
        strengthBar.className = 'progress-bar ' + color;
        feedback.textContent = message;
    });

    // Confirmation before leaving with unsaved changes
    let formChanged = false;
    
    document.querySelectorAll('form input, form select').forEach(function(element) {
        element.addEventListener('change', function() {
            formChanged = true;
        });
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            formChanged = false;
        });
    });
</script>
@endpush

<style>
    .card {
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    
    .input-group .btn {
        border-left: none;
    }
    
    .progress {
        background-color: #e9ecef;
    }
    
    .alert ul {
        padding-left: 1.5rem;
        margin-bottom: 0;
    }
    
    .alert ul li {
        margin-bottom: 0.25rem;
    }
</style>
@endsection