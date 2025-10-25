@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-plus-circle me-2"></i>Nuevo Contenido</h1>
                <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver a Biblioteca
                </a>
            </div>

            <!-- Formulario -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Información del Contenido</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.content.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Título -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">
                                <i class="fas fa-heading"></i> Título <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   placeholder="Ej: Introducción al Álgebra"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Materia -->
                            <div class="col-md-4 mb-4">
                                <label for="subject_area" class="form-label fw-bold">
                                    <i class="fas fa-book"></i> Materia <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('subject_area') is-invalid @enderror" 
                                        id="subject_area" 
                                        name="subject_area" 
                                        required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject }}" {{ old('subject_area') == $subject ? 'selected' : '' }}>
                                            {{ $subject }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subject_area')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tipo -->
                            <div class="col-md-4 mb-4">
                                <label for="type" class="form-label fw-bold">
                                    <i class="fas fa-folder"></i> Tipo <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" 
                                        name="type" 
                                        required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Dificultad -->
                            <div class="col-md-4 mb-4">
                                <label for="difficulty_level" class="form-label fw-bold">
                                    <i class="fas fa-signal"></i> Dificultad <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('difficulty_level') is-invalid @enderror" 
                                        id="difficulty_level" 
                                        name="difficulty_level" 
                                        required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($difficulties as $difficulty)
                                        <option value="{{ $difficulty }}" {{ old('difficulty_level') == $difficulty ? 'selected' : '' }}>
                                            {{ $difficulty }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('difficulty_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">
                                <i class="fas fa-align-left"></i> Descripción
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Describe brevemente el contenido...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- URL del Contenido -->
                        <div class="mb-4">
                            <label for="content_url" class="form-label fw-bold">
                                <i class="fas fa-link"></i> URL del Contenido (Video/Documento)
                            </label>
                            <input type="url" 
                                   class="form-control @error('content_url') is-invalid @enderror" 
                                   id="content_url" 
                                   name="content_url" 
                                   value="{{ old('content_url') }}"
                                   placeholder="https://www.youtube.com/watch?v=... o https://drive.google.com/...">
                            @error('content_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Soporta: YouTube, Vimeo, Google Drive, Dropbox, enlaces directos a PDFs, etc.
                            </small>
                        </div>

                        <!-- Estado -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="active" 
                                       name="active" 
                                       value="1"
                                       {{ old('active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="active">
                                    <i class="fas fa-toggle-on"></i> Contenido Activo
                                </label>
                            </div>
                            <small class="text-muted">Si está activo, el contenido estará disponible para los estudiantes</small>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="{{ route('admin.content.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Guardar Contenido
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
}
</style>
@endsection