@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-eye me-2"></i>Ver Contenido</h1>
                <div>
                    <a href="{{ route('admin.content.edit', $content->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                    <a href="{{ route('admin.content.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="row">
                <!-- Información del Contenido -->
                <div class="col-md-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información General</h5>
                        </div>
                        <div class="card-body">
                            <h2 class="mb-3">{{ $content->title }}</h2>
                            
                            <div class="mb-4">
                                <span class="badge bg-info me-2">{{ $content->subject_area }}</span>
                                <span class="badge bg-secondary me-2">{{ $content->type }}</span>
                                <span class="badge bg-warning">{{ $content->difficulty_level }}</span>
                                @if($content->active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </div>

                            @if($content->description)
                            <div class="mb-4">
                                <h6 class="fw-bold"><i class="fas fa-align-left me-2"></i>Descripción:</h6>
                                <p class="text-muted">{{ $content->description }}</p>
                            </div>
                            @endif

                            @if($content->content_url)
                            <div class="mb-4">
                                <h6 class="fw-bold"><i class="fas fa-link me-2"></i>URL del Contenido:</h6>
                                <a href="{{ $content->content_url }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i>Abrir Contenido
                                </a>
                                <div class="mt-2">
                                    <small class="text-muted">{{ $content->content_url }}</small>
                                </div>
                            </div>
                            @endif

                            <!-- Preview del Contenido -->
                            @if($content->content_url)
                            <div class="mb-4">
                                <h6 class="fw-bold"><i class="fas fa-play-circle me-2"></i>Vista Previa:</h6>
                                <div class="ratio ratio-16x9">
                                    @if(str_contains($content->content_url, 'youtube.com') || str_contains($content->content_url, 'youtu.be'))
                                        @php
                                            preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $content->content_url, $matches);
                                            $videoId = $matches[1] ?? null;
                                        @endphp
                                        @if($videoId)
                                            <iframe src="https://www.youtube.com/embed/{{ $videoId }}" 
                                                    frameborder="0" 
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                    allowfullscreen>
                                            </iframe>
                                        @endif
                                    @elseif(str_contains($content->content_url, 'vimeo.com'))
                                        @php
                                            preg_match('/vimeo\.com\/(\d+)/', $content->content_url, $matches);
                                            $videoId = $matches[1] ?? null;
                                        @endphp
                                        @if($videoId)
                                            <iframe src="https://player.vimeo.com/video/{{ $videoId }}" 
                                                    frameborder="0" 
                                                    allow="autoplay; fullscreen; picture-in-picture" 
                                                    allowfullscreen>
                                            </iframe>
                                        @endif
                                    @elseif(str_contains($content->content_url, 'drive.google.com'))
                                        <iframe src="{{ $content->content_url }}" 
                                                frameborder="0" 
                                                allowfullscreen>
                                        </iframe>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-light">
                                            <div class="text-center">
                                                <i class="fas fa-file fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Vista previa no disponible</p>
                                                <a href="{{ $content->content_url }}" target="_blank" class="btn btn-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i>Abrir Contenido
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Panel Lateral -->
                <div class="col-md-4">
                    <!-- Estadísticas -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Estadísticas</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">Vistas</small>
                                <h4>{{ $content->views ?? 0 }}</h4>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Creado</small>
                                <p class="mb-0">{{ \Carbon\Carbon::parse($content->created_at)->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <small class="text-muted">Última Actualización</small>
                                <p class="mb-0">{{ \Carbon\Carbon::parse($content->updated_at)->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Acciones</h6>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('admin.content.edit', $content->id) }}" 
                               class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-edit me-1"></i>Editar Contenido
                            </a>
                            
                            @if($content->content_url)
                            <a href="{{ $content->content_url }}" 
                               target="_blank" 
                               class="btn btn-outline-info w-100 mb-2">
                                <i class="fas fa-external-link-alt me-1"></i>Abrir en Nueva Pestaña
                            </a>
                            @endif
                            
                            <form method="POST" 
                                  action="{{ route('admin.content.destroy', $content->id) }}"
                                  onsubmit="return confirm('¿Estás seguro de eliminar este contenido? Esta acción no se puede deshacer.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-trash me-1"></i>Eliminar Contenido
                                </button>
                            </form>
                        </div>
                    </div>
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