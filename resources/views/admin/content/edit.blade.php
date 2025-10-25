@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-edit me-2"></i>Editar Contenido</h1>
                <div>
                    <a href="{{ route('admin.content.show', $content->id) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye me-1"></i>Ver
                    </a>
                    <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>

            <!-- Formulario -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Editar Información</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.content.update', $content->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Título -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">
                                <i class="fas fa-heading"></i> Título <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $content->title) }}"
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
                                        <option value="{{ $subject }}" 
                                                {{ old('subject_area', $content->subject_area) == $subject ? 'selected' : '' }}>
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
                                        <option value="{{ $type }}" 
                                                {{ old('type', $content->type) == $type ? 'selected' : '' }}>
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
                                        <option value="{{ $difficulty }}" 
                                                {{ old('difficulty_level', $content->difficulty_level) == $difficulty ? 'selected' : '' }}>
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
                                      rows="4">{{ old('description', $content->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- URL del Contenido -->
                        <div class="mb-4">
                            <label for="externel_url" class="form-label fw-bold">
                                <i class="fas fa-link"></i> URL del Contenido (Video/Documento)
                            </label>
                            <input type="url" 
                                   class="form-control @error('external_url') is-invalid @enderror" 
                                   id="external_url" 
                                   name="external_url" 
                                   value="{{ old('external_url', $content->external_url) }}">
                            @error('external_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Soporta: YouTube, Vimeo, Google Drive, Dropbox, enlaces directos a PDFs, etc.
                            </small>
                            @if($content->external_url)
                            <div class="mt-2">
                                <a href="{{ $content->external_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i>Ver enlace actual
                                </a>
                            </div>
                            @endif
                        </div>

                        <!-- Estado -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="active" 
                                       name="active" 
                                       value="1"
                                       {{ old('active', $content->active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="active">
                                    <i class="fas fa-toggle-on"></i> Contenido Activo
                                </label>
                            </div>
                            <small class="text-muted">Si está activo, el contenido estará disponible para los estudiantes</small>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <div>
                                <a href="{{ route('admin.content.show', $content->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i>Actualizar Contenido
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview del Contenido -->
            @if($content->external_url)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Vista Previa del Contenido</h5>
                </div>
                <div class="card-body">
                    <div class="ratio ratio-16x9">
                        @if(str_contains($content->external_url, 'youtube.com') || str_contains($content->external_url, 'youtu.be'))
                            @php
                                preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $content->external_url, $matches);
                                $videoId = $matches[1] ?? null;
                            @endphp
                            @if($videoId)
                                <iframe src="https://www.youtube.com/embed/{{ $videoId }}" 
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                        allowfullscreen>
                                </iframe>
                            @endif
                        @elseif(str_contains($content->external_url, 'vimeo.com'))
                            @php
                                preg_match('/vimeo\.com\/(\d+)/', $content->external_url, $matches);
                                $videoId = $matches[1] ?? null;
                            @endphp
                            @if($videoId)
                                <iframe src="https://player.vimeo.com/video/{{ $videoId }}" 
                                        frameborder="0" 
                                        allow="autoplay; fullscreen; picture-in-picture" 
                                        allowfullscreen>
                                </iframe>
                            @endif
                        @elseif(str_contains($content->external_url, 'drive.google.com'))
                            <iframe src="{{ $content->external_url }}" 
                                    frameborder="0" 
                                    allowfullscreen>
                            </iframe>
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-light">
                                <div class="text-center">
                                    <i class="fas fa-file fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Vista previa no disponible</p>
                                    <a href="{{ $content->external_url }}" target="_blank" class="btn btn-primary">
                                        <i class="fas fa-external-link-alt me-1"></i>Abrir Contenido
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.card {
    border: none;
}
</style>
@endsection