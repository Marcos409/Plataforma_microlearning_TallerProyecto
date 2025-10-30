@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Editar Usuario: {{ $user->name }}</h4>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Información Personal -->
                            <div class="col-md-6">
                                <h5 class="mb-3 text-primary">Información Personal</h5>
                                
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $user->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
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

                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">Nueva Contraseña</label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password">
                                    <small class="form-text text-muted">Dejar en blanco para mantener la contraseña actual</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $user->phone) }}" 
                                           placeholder="Ej: +56 9 1234 5678">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Información del Sistema -->
                            <div class="col-md-6">
                                <h5 class="mb-3 text-primary">Información del Sistema</h5>
                                
                                <div class="form-group mb-3">
                                    <label for="role_id" class="form-label">Rol del Usuario <span class="text-danger">*</span></label>
                                    <select class="form-control @error('role_id') is-invalid @enderror" 
                                            id="role_id" 
                                            name="role_id" 
                                            required>
                                        <option value="">Seleccionar rol</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="student-fields" class="{{ $user->role && $user->role->name === 'Estudiante' ? '' : 'd-none' }}">
                                    <div class="form-group mb-3">
                                        <label for="student_code" class="form-label">Código de Estudiante</label>
                                        <input type="text" 
                                               class="form-control @error('student_code') is-invalid @enderror" 
                                               id="student_code" 
                                               name="student_code" 
                                               value="{{ old('student_code', $user->student_code) }}"
                                               placeholder="Ej: EST001">
                                        @error('student_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="career" class="form-label">Carrera</label>
                                        <select class="form-control @error('career') is-invalid @enderror" 
                                                id="career" 
                                                name="career">
                                            <option value="">Seleccionar carrera</option>
                                            <option value="Ingeniería de Sistemas" {{ old('career', $user->career) == 'Ingeniería de Sistemas' ? 'selected' : '' }}>Ingeniería de Sistemas</option>
                                            <option value="Ingeniería Industrial" {{ old('career', $user->career) == 'Ingeniería Industrial' ? 'selected' : '' }}>Ingeniería Industrial</option>
                                            <option value="Ingeniería Civil" {{ old('career', $user->career) == 'Ingeniería Civil' ? 'selected' : '' }}>Ingeniería Civil</option>
                                            <option value="Administración" {{ old('career', $user->career) == 'Administración' ? 'selected' : '' }}>Administración</option>
                                            <option value="Contabilidad" {{ old('career', $user->career) == 'Contabilidad' ? 'selected' : '' }}>Contabilidad</option>
                                            <option value="Marketing" {{ old('career', $user->career) == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                                            <option value="Psicología" {{ old('career', $user->career) == 'Psicología' ? 'selected' : '' }}>Psicología</option>
                                            <option value="Derecho" {{ old('career', $user->career) == 'Derecho' ? 'selected' : '' }}>Derecho</option>
                                        </select>
                                        @error('career')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="semester" class="form-label">Semestre</label>
                                        <select class="form-control @error('semester') is-invalid @enderror" 
                                                id="semester" 
                                                name="semester">
                                            <option value="">Seleccionar semestre</option>
                                            @for($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}" {{ old('semester', $user->semester) == $i ? 'selected' : '' }}>
                                                    {{ $i }}° Semestre
                                                </option>
                                            @endfor
                                        </select>
                                        @error('semester')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="active" 
                                               name="active" 
                                               value="1" 
                                               {{ old('active', $user->active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="active">
                                            Usuario activo
                                        </label>
                                    </div>
                                </div>

                                <!-- Información de auditoría -->
                                <div class="form-group mb-3">
                                    <label class="form-label">Información del Registro</label>
                                    <div class="form-control-plaintext">
                                        <small class="text-muted">
                                            <strong>Creado:</strong> {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}<br>
                                            <strong>Actualizado:</strong> {{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : 'N/A' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save"></i> Actualizar Usuario
                                        </button>
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-lg ml-2">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-lg">
                                            <i class="fas fa-eye"></i> Ver Perfil
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role_id');
        const studentFields = document.getElementById('student-fields');
        
        if (roleSelect && studentFields) {
            // Función para mostrar/ocultar campos de estudiante
            function toggleStudentFields() {
                const selectedRoleId = roleSelect.value;
                
                // 3 es el ID del rol Estudiante
                if (selectedRoleId == '3') {
                    studentFields.classList.remove('d-none');
                } else {
                    studentFields.classList.add('d-none');
                }
            }
            
            // Ejecutar al cargar la página para mostrar el estado inicial correcto
            toggleStudentFields();
            
            // Ejecutar cuando cambie el select
            roleSelect.addEventListener('change', toggleStudentFields);
        }
    });
</script>
@endsection