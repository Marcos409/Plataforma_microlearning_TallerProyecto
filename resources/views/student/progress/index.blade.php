@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Mi Progreso Académico</h4>
                </div>

                <div class="card-body">
                    <!-- Resumen General -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center bg-primary text-white">
                                <div class="card-body">
                                    <h3>{{ $totalActivities ?? 0 }}</h3>
                                    <p class="mb-0">Actividades Completadas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center bg-success text-white">
                                <div class="card-body">
                                    <h3>{{ number_format($overallProgress ?? 0, 1) }}%</h3>
                                    <p class="mb-0">Progreso General</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center bg-info text-white">
                                <div class="card-body">
                                    <h3>{{ floor(($totalTimeSpent ?? 0) / 60) }}</h3>
                                    <p class="mb-0">Horas de Estudio</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center bg-warning text-white">
                                <div class="card-body">
                                    <h3>{{ $recentActivities ?? 0 }}</h3>
                                    <p class="mb-0">Esta Semana</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progreso por Materia -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>Progreso por Materia</h5>
                            @if($progressBySubject && count($progressBySubject) > 0)
                                @foreach($progressBySubject as $subject => $progress)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><strong>{{ $subject }}</strong></span>
                                        <span>{{ number_format($progress['percentage'], 1) }}%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $progress['percentage'] }}%" 
                                             aria-valuenow="{{ $progress['percentage'] }}" 
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        {{ $progress['completed'] }} de {{ $progress['total'] }} actividades completadas
                                    </small>
                                </div>
                                @endforeach
                            @else
                                <div class="alert alert-info">
                                    <p>Aún no tienes progreso registrado en ninguna materia.</p>
                                    <a href="{{ route('student.content.index') }}" class="btn btn-primary">
                                        Explorar Contenidos
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actividad Reciente -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Actividad Reciente</h5>
                            @if($recentProgress && count($recentProgress) > 0)
                                <div class="list-group">
                                    @foreach($recentProgress as $activity)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-1">{{ $activity->subject_area }}</h6>
                                                <p class="mb-1">{{ $activity->topic ?? 'Actividad general' }}</p>
                                                <small class="text-muted">
                                                    {{ $activity->completed_activities }} actividades completadas
                                                </small>
                                            </div>
                                            <small class="text-muted">
                                                {{ $activity->last_activity ? \Carbon\Carbon::parse($activity->last_activity)->diffForHumans() : 'Sin fecha' }}
                                            </small>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    No hay actividad reciente registrada.
                                </div>
                            @endif
                        </div>

                        <!-- Diagnósticos Completados -->
                        <div class="col-md-6">
                            <h5>Diagnósticos Completados</h5>
                            @if($diagnosticResults && count($diagnosticResults) > 0)
                                <div class="list-group">
                                    @foreach($diagnosticResults as $result)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-1">Diagnóstico #{{ $result->diagnostic_id ?? 'N/A' }}</h6>
                                                <p class="mb-1">Puntuación: {{ $result->score ?? 'N/A' }}</p>
                                                <small class="text-muted">
                                                    {{ $result->completed_at ? \Carbon\Carbon::parse($result->completed_at)->format('d/m/Y') : 'Sin fecha' }}
                                                </small>
                                            </div>
                                            <span class="badge badge-{{ ($result->score ?? 0) >= 70 ? 'success' : 'warning' }}">
                                                {{ ($result->score ?? 0) >= 70 ? 'Aprobado' : 'Necesita mejora' }}
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <p>No has completado diagnósticos aún.</p>
                                    <a href="{{ route('student.diagnostics.index') }}" class="btn btn-primary btn-sm">
                                        Ver Diagnósticos
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Rutas de Aprendizaje -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Mis Rutas de Aprendizaje</h5>
                            @if($learningPaths && count($learningPaths) > 0)
                                <div class="row">
                                    @foreach($learningPaths as $path)
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">Ruta #{{ $path->id }}</h6>
                                                <p class="card-text">
                                                    Progreso: {{ number_format($path->progress_percentage ?? 0, 1) }}%
                                                </p>
                                                <div class="progress mb-2">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: {{ $path->progress_percentage ?? 0 }}%">
                                                    </div>
                                                </div>
                                                <a href="{{ route('student.learning-paths.show', $path->id) }}" 
                                                   class="btn btn-sm btn-primary">Ver Detalle</a>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <p>No tienes rutas de aprendizaje asignadas.</p>
                                    <a href="{{ route('student.learning-paths.index') }}" class="btn btn-primary">
                                        Explorar Rutas
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Enlaces de acción -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <a href="{{ route('student.content.index') }}" class="btn btn-primary mx-2">
                                <i class="fas fa-book"></i> Explorar Contenidos
                            </a>
                            <a href="{{ route('student.diagnostics.index') }}" class="btn btn-success mx-2">
                                <i class="fas fa-clipboard-list"></i> Realizar Diagnóstico
                            </a>
                            <a href="{{ route('student.recommendations.index') }}" class="btn btn-info mx-2">
                                <i class="fas fa-lightbulb"></i> Ver Recomendaciones
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection