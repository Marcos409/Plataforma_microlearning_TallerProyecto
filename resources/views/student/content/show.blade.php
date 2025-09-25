@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ $content->title }}</h4>
                    <a href="{{ route('student.content.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <span class="badge badge-info">{{ $content->subject_area }}</span>
                            <span class="badge badge-secondary">{{ $content->type }}</span>
                            <span class="badge badge-warning">{{ $content->difficulty_level }}</span>
                            @if($recommendation)
                                <span class="badge badge-primary">Recomendado</span>
                            @endif
                        </div>
                    </div>

                    @if($content->description)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>Descripción</h5>
                            <p>{{ $content->description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Área del contenido -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="content-area bg-light p-4 rounded">
                                @if($content->content_url)
                                    @if($content->type == 'Video')
                                        <div class="embed-responsive embed-responsive-16by9">
                                            <iframe class="embed-responsive-item" src="{{ $content->content_url }}" allowfullscreen></iframe>
                                        </div>
                                    @elseif($content->type == 'Documento')
                                        <a href="{{ $content->content_url }}" target="_blank" class="btn btn-primary btn-lg">
                                            <i class="fas fa-file-download"></i> Descargar Documento
                                        </a>
                                    @else
                                        <a href="{{ $content->content_url }}" target="_blank" class="btn btn-primary btn-lg">
                                            <i class="fas fa-external-link-alt"></i> Acceder al Contenido
                                        </a>
                                    @endif
                                @else
                                    <div class="text-center">
                                        <h5>Contenido en desarrollo</h5>
                                        <p>Este contenido estará disponible próximamente.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Botón para marcar como completado -->
                    <div class="row mb-4">
                        <div class="col-md-12 text-center">
                            <button class="btn btn-success btn-lg" onclick="markAsComplete({{ $content->id }})">
                                <i class="fas fa-check"></i> Marcar como Completado
                            </button>
                        </div>
                    </div>

                    <!-- Contenidos relacionados -->
                    @if($relatedContents && $relatedContents->count() > 0)
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Contenidos Relacionados</h5>
                            <div class="row">
                                @foreach($relatedContents as $related)
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $related->title }}</h6>
                                            <p class="card-text">
                                                {{ Str::limit($related->description ?? '', 50) }}
                                            </p>
                                            <a href="{{ route('student.content.show', $related->id) }}" 
                                               class="btn btn-sm btn-outline-primary">Ver</a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAsComplete(contentId) {
    fetch(`/student/content/${contentId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            time_spent: Math.floor(Math.random() * 1800) + 300 // Tiempo simulado entre 5-35 minutos
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('¡Contenido marcado como completado!');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al marcar como completado');
    });
}
</script>
@endsection