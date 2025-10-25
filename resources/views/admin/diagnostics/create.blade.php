@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-plus-circle me-2"></i>Crear Nuevo Diagnóstico</h2>
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

            <form method="POST" action="{{ route('admin.diagnostics.store') }}">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Título <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                           value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Área de Materia <span class="text-danger">*</span></label>
                    <select name="subject_area" class="form-select @error('subject_area') is-invalid @enderror" required>
                        <option value="">Selecciona una materia...</option>
                        <option value="Matemáticas" {{ old('subject_area') == 'Matemáticas' ? 'selected' : '' }}>Matemáticas</option>
                        <option value="Física" {{ old('subject_area') == 'Física' ? 'selected' : '' }}>Física</option>
                        <option value="Química" {{ old('subject_area') == 'Química' ? 'selected' : '' }}>Química</option>
                        <option value="Programación" {{ old('subject_area') == 'Programación' ? 'selected' : '' }}>Programación</option>
                        <option value="Inglés" {{ old('subject_area') == 'Inglés' ? 'selected' : '' }}>Inglés</option>
                    </select>
                    @error('subject_area')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Puntaje Mínimo para Aprobar (%) <span class="text-danger">*</span></label>
                    <input type="number" name="passing_score" class="form-control @error('passing_score') is-invalid @enderror" 
                           value="{{ old('passing_score', 60) }}" min="1" max="100" required>
                    <small class="text-muted">Porcentaje mínimo requerido para aprobar el diagnóstico (1-100)</small>
                    @error('passing_score')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                              rows="4" placeholder="Describe el propósito y contenido del diagnóstico...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Nota:</strong> Después de crear el diagnóstico, podrás agregar las preguntas correspondientes.
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.diagnostics.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Crear Diagnóstico
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection