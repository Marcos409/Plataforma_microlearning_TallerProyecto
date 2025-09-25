@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Diagnósticos Disponibles</h1>
</div>

<div class="row">
    @forelse($diagnostics as $diagnostic)
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $diagnostic->title }}</h5>
                    @if($completedDiagnostics->contains($diagnostic->id))
                        <span class="badge bg-success">Completado</span>
                    @else
                        <span class="badge bg-warning">Pendiente</span>
                    @endif
                </div>
                <div class="card-body">
                    <p class="card-text">{{ $diagnostic->description }}</p>
                    
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <small class="text-muted">
                                <i class="fas fa-book"></i> {{ $diagnostic->subject_area }}
                            </small>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">
                                <i class="fas fa-question-circle"></i> {{ $diagnostic->total_questions }} preguntas
                            </small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <small class="text-muted">
                                <i class="fas fa-target"></i> Puntaje mínimo: {{ $diagnostic->passing_score }}%
                            </small>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> ~{{ $diagnostic->total_questions * 2 }} minutos
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    @if($completedDiagnostics->contains($diagnostic->id))
                        <a href="{{ route('student.diagnostics.result', $diagnostic) }}" class="btn btn-outline-success me-2">
                            <i class="fas fa-chart-bar"></i> Ver Resultado
                        </a>
                        <a href="{{ route('student.diagnostics.show', $diagnostic) }}" class="btn btn-outline-primary">
                            <i class="fas fa-redo"></i> Repetir
                        </a>
                    @else
                        <a href="{{ route('student.diagnostics.show', $diagnostic) }}" class="btn btn-primary">
                            <i class="fas fa-play"></i> Comenzar Diagnóstico
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-clipboard-check fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No hay diagnósticos disponibles</h4>
                <p class="text-muted">Los diagnósticos aparecerán aquí cuando estén disponibles.</p>
            </div>
        </div>
    @endforelse
</div>

@if($diagnostics->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle"></i> Información importante:</h6>
            <ul class="mb-0">
                <li>Los diagnósticos te ayudan a identificar áreas de mejora</li>
                <li>Basándose en tus resultados, se generarán recomendaciones personalizadas</li>
                <li>Puedes repetir un diagnóstico cuando lo desees</li>
                <li>Tus respuestas se guardan automáticamente</li>
            </ul>
        </div>
    </div>
</div>
@endif
@endsection