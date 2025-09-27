@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Progreso en {{ $subject ?? 'Materia' }}</h4>
                    <a href="{{ route('student.progress.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    @if(isset($subjectProgress) && count($subjectProgress) > 0)
                        <!-- Resumen de la materia -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h5>Resumen de {{ $subject }}</h5>
                                    <p>Tu progreso detallado en esta materia.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Progreso por tema -->
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Progreso por Tema</h5>
                                @foreach($subjectProgress as $progress)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6>{{ $progress->topic ?? 'Tema general' }}</h6>
                                                <p class="text-muted mb-2">
                                                    {{ $progress->completed_activities }} de {{ $progress->total_activities }} actividades completadas
                                                </p>
                                                
                                                <div class="progress mb-2">
                                                    <div class="progress-bar 
                                                        @if($progress->progress_percentage >= 80) bg-success
                                                        @elseif($progress->progress_percentage >= 60) bg-info  
                                                        @elseif($progress->progress_percentage >= 40) bg-warning
                                                        @else bg-danger @endif" 
                                                         role="progressbar" 
                                                         style="width: {{ $progress->progress_percentage }}%">
                                                        {{ number_format($progress->progress_percentage, 1) }}%
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock"></i>
                                                            Tiempo: {{ floor($progress->total_time_spent / 60) }}h {{ $progress->total_time_spent % 60 }}m
                                                        </small>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted">
                                                            <i class="fas fa-star"></i>
                                                            Promedio: {{ number_format($progress->average_score, 1) }}%
                                                        </small>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar"></i>
                                                            Última actividad: {{ $progress->last_activity ? \Carbon\Carbon::parse($progress->last_activity)->format('d/m/Y') : 'N/A' }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="ml-3">
                                                @if($progress->progress_percentage >= 80)
                                                    <span class="badge badge-success badge-pill">Excelente</span>
                                                @elseif($progress->progress_percentage >= 60)
                                                    <span class="badge badge-info badge-pill">Bueno</span>
                                                @elseif($progress->progress_percentage >= 40)
                                                    <span class="badge badge-warning badge-pill">Regular</span>
                                                @else
                                                    <span class="badge badge-danger badge-pill">Necesita atención</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Recomendaciones -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-light">
                                    <h6><i class="fas fa-lightbulb text-warning"></i> Recomendaciones</h6>
                                    <ul class="mb-0">
                                        @php
                                            $averageProgress = collect($subjectProgress)->avg('progress_percentage');
                                        @endphp
                                        @if($averageProgress < 50)
                                            <li>Dedica más tiempo a practicar en esta materia</li>
                                            <li>Considera revisar los contenidos básicos</li>
                                        @elseif($averageProgress < 80)
                                            <li>¡Vas bien! Continúa con el buen trabajo</li>
                                            <li>Enfócate en los temas con menor progreso</li>
                                        @else
                                            <li>¡Excelente trabajo en esta materia!</li>
                                            <li>Podrías ayudar a otros compañeros</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h5>Sin progreso registrado</h5>
                            <p>Aún no tienes progreso registrado en esta materia.</p>
                            <a href="{{ route('student.content.index') }}" class="btn btn-primary">
                                Explorar Contenidos de {{ $subject }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection