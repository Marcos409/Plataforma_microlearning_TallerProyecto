@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Editar Estudiante</h4>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
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

                    <form method="POST" action="{{ route('admin.students.update', $student->id) }}">
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
                                           value="{{ old('name', $student->name) }}" 
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
                                           value="{{ old('email', $student->email) }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="student_code" class="form-label">Código de Estudiante <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('student_code') is-invalid @enderror" 
                                           id="student_code" 
                                           name="student_code" 
                                           value="{{ old('student_code', $student->student_code) }}" 
                                           required>
                                    @error('student_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $student->phone) }}" 
                                           placeholder="Ej: +51 999 888 777">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Información Académica -->
                            <div class="col-md-6">
                                <h5 class="mb-3 text-primary">Información Académica</h5>
                                
                                <div class="form-group mb-3">
                                    <label for="career" class="form-label">Carrera <span class="text-danger">*</span></label>
                                    <select class="form-control @error('career') is-invalid @enderror" 
                                            id="career" 
                                            name="career" 
                                            required>
                                        <option value="">Seleccionar carrera</option>
                                        <option value="Ingeniería de Sistemas" {{ old('career', $student->career) == 'Ingeniería de Sistemas' ? 'selected' : '' }}>Ingeniería de Sistemas</option>
                                        <option value="Ingeniería Industrial" {{ old('career', $student->career) == 'Ingeniería Industrial' ? 'selected' : '' }}>Ingeniería Industrial</option>
                                        <option value="Ingeniería Civil" {{ old('career', $student->career) == 'Ingeniería Civil' ? 'selected' : '' }}>Ingeniería Civil</option>
                                        <option value="Administración" {{ old('career', $student->career) == 'Administración' ? 'selected' : '' }}>Administración</option>
                                        <option value="Contabilidad" {{ old('career', $student->career) == 'Contabilidad' ? 'selected' : '' }}>Contabilidad</option>
                                        <option value="Marketing" {{ old('career', $student->career) == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                                        <option value="Psicología" {{ old('career', $student->career) == 'Psicología' ? 'selected' : '' }}>Psicología</option>
                                        <option value="Derecho" {{ old('career', $student->career) == 'Derecho' ? 'selected' : '' }}>Derecho</option>
                                    </select>
                                    @error('career')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="semester" class="form-label">Semestre <span class="text-danger">*</span></label>
                                    <select class="form-control @error('semester') is-invalid @enderror" 
                                            id="semester" 
                                            name="semester" 
                                            required>
                                        <option value="">Seleccionar semestre</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ old('semester', $student->semester) == $i ? 'selected' : '' }}>
                                                {{ $i }}° Semestre
                                            </option>
                                        @endfor
                                    </select>
                                    @error('semester')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">Estado del Estudiante</label>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="active" 
                                               name="active" 
                                               value="1" 
                                               {{ old('active', $student->active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="active">
                                            Estudiante activo
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Los estudiantes inactivos no podrán acceder al sistema.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Información adicional -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="mb-3 text-primary">Información del Registro</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Fecha de Registro</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   value="{{ $student->created_at ? \Carbon\Carbon::parse($student->created_at)->format('d/m/Y H:i') : 'N/A' }}" 
                                                   readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Última Actualización</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   value="{{ $student->updated_at ? \Carbon\Carbon::parse($student->updated_at)->format('d/m/Y H:i') : 'N/A' }}" 
                                                   readonly>
                                        </div>
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
                                            <i class="fas fa-save"></i> Actualizar Estudiante
                                        </button>
                                        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary btn-lg ml-2">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-info btn-lg">
                                            <i class="fas fa-eye"></i> Ver Perfil
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tarjeta de información adicional -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Información Importante</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Notas sobre la edición:</h6>
                        <ul class="mb-0">
                            <li>Los campos marcados con <span class="text-danger">*</span> son obligatorios.</li>
                            <li>El correo electrónico debe ser único en el sistema.</li>
                            <li>El código de estudiante debe ser único y seguir el formato institucional.</li>
                            <li>Si desactivas al estudiante, no podrá acceder al sistema hasta ser reactivado.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación adicional del lado del cliente
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const phoneInput = document.getElementById('phone');
    
    // Validar formato de teléfono
    phoneInput.addEventListener('input', function() {
        const phone = this.value.replace(/\D/g, '');
        if (phone.length > 0 && phone.length < 9) {
            this.setCustomValidity('El teléfono debe tener al menos 9 dígitos');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Confirmar antes de enviar
    form.addEventListener('submit', function(e) {
        const isActive = document.getElementById('active').checked;
        if (!isActive) {
            if (!confirm('¿Estás seguro de desactivar este estudiante? No podrá acceder al sistema.')) {
                e.preventDefault();
            }
        }
    });
});
</script>
@endsection