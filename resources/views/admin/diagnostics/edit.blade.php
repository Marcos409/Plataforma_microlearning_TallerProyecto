@extends('layouts.app')

@section('title', 'Editar Diagnóstico')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-edit me-2"></i>Editar Diagnóstico</h2>
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

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.diagnostics.update', $diagnostic) }}">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Columna Izquierda -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   value="{{ old('title', $diagnostic->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Área de Materia <span class="text-danger">*</span></label>
                            <select name="subject_area" class="form-select @error('subject_area') is-invalid @enderror" required>
                                <option value="">Selecciona una materia...</option>
                                <option value="Matemáticas" {{ old('subject_area', $diagnostic->subject_area) == 'Matemáticas' ? 'selected' : '' }}>Matemáticas</option>
                                <option value="Física" {{ old('subject_area', $diagnostic->subject_area) == 'Física' ? 'selected' : '' }}>Física</option>
                                <option value="Química" {{ old('subject_area', $diagnostic->subject_area) == 'Química' ? 'selected' : '' }}>Química</option>
                                <option value="Programación" {{ old('subject_area', $diagnostic->subject_area) == 'Programación' ? 'selected' : '' }}>Programación</option>
                                <option value="Inglés" {{ old('subject_area', $diagnostic->subject_area) == 'Inglés' ? 'selected' : '' }}>Inglés</option>
                                <option value="Biología" {{ old('subject_area', $diagnostic->subject_area) == 'Biología' ? 'selected' : '' }}>Biología</option>
                            </select>
                            @error('subject_area')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Puntaje Mínimo para Aprobar (%) <span class="text-danger">*</span></label>
                            <input type="number" name="passing_score" 
                                   class="form-control @error('passing_score') is-invalid @enderror" 
                                   value="{{ old('passing_score', $diagnostic->passing_score) }}" 
                                   min="1" max="100" required>
                            <small class="text-muted">Porcentaje mínimo requerido para aprobar (1-100)</small>
                            @error('passing_score')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Columna Derecha -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="6" 
                                      placeholder="Describe el propósito y contenido del diagnóstico...">{{ old('description', $diagnostic->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="active" 
                                       id="active" value="1" 
                                       {{ old('active', $diagnostic->active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">
                                    <strong>Diagnóstico Activo</strong>
                                    <br><small class="text-muted">Los estudiantes podrán acceder y realizar este diagnóstico</small>
                                </label>
                            </div>
                        </div>

                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fas fa-info-circle me-1"></i>Información</h6>
                                <small class="text-muted">
                                    <strong>Creado:</strong> {{ $diagnostic->created_at->format('d/m/Y H:i') }}<br>
                                    <strong>Última actualización:</strong> {{ $diagnostic->updated_at->format('d/m/Y H:i') }}<br>
                                    <strong>Total de preguntas:</strong> {{ $diagnostic->questions->count() }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('admin.diagnostics.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <a href="{{ route('admin.diagnostics.show', $diagnostic) }}" class="btn btn-info">
                            <i class="fas fa-eye me-1"></i>Ver Detalle
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('admin.diagnostics.questions.index', $diagnostic) }}" class="btn btn-success me-2">
                            <i class="fas fa-question-circle me-1"></i>Gestionar Preguntas
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection