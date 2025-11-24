@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Detalle del Estudiante</h1>
        <div>
            <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información Personal -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Información Personal</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Nombre:</strong></div>
                        <div class="col-sm-7">{{ $student->name }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Email:</strong></div>
                        <div class="col-sm-7">{{ $student->email }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Código:</strong></div>
                        <div class="col-sm-7">{{ $student->student_code ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Carrera:</strong></div>
                        <div class="col-sm-7">{{ $student->career ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Semestre:</strong></div>
                        <div class="col-sm-7">{{ $student->semester ?? 'N/A' }}°</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Teléfono:</strong></div>
                        <div class="col-sm-7">{{ $student->phone ?? 'No registrado' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Estado:</strong></div>
                        <div class="col-sm-7">
                            <span class="badge bg-{{ $student->active ? 'success' : 'danger' }}">
                                {{ $student->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Registro:</strong></div>
                        <div class="col-sm-7">
                            @if($student->created_at)
                                {{ $student->created_at->format('d/m/Y H:i') }}
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-5"><strong>Última actividad:</strong></div>
                        <div class="col-sm-7">
                            @if($student->last_activity)
                                {{ $student->last_activity->diffForHumans() }}
                            @else
                                Nunca
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Progreso -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Progreso Académico</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h3 text-primary">
                                    {{ number_format($student->stats->progreso_general->completion_rate ?? 0, 1) }}%
                                </div>
                                <small class="text-muted">Progreso General</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h3 text-success">
                                    {{ $student->stats->progreso_general->completed_modules ?? 0 }}
                                </div>
                                <small class="text-muted">Módulos Completados</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h3 text-info">
                                    {{ $student->stats->diagnosticos->completed_diagnostics ?? 0 }}
                                </div>
                                <small class="text-muted">Diagnósticos Completados</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h3 text-warning">
                                    {{ $student->stats->recomendaciones->pending_recommendations ?? 0 }}
                                </div>
                                <small class="text-muted">Recomendaciones Pendientes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progreso por Materia -->
            @if($student->studentProgress && $student->studentProgress->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-book"></i> Progreso por Materia</h5>
                </div>
                <div class="card-body">
                    @foreach($student->studentProgress->groupBy('subject_area') as $subject => $progress)
                        <div class="mb-3">
                            <h6>{{ $subject }}</h6>
                            @foreach($progress as $item)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small">{{ $item->topic ?? $item->title ?? 'Sin título' }}</span>
                                    <span class="small">{{ number_format($item->progress_percentage ?? 0, 1) }}%</span>
                                </div>
                                <div class="progress mb-2" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $item->progress_percentage ?? 0 }}%"></div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Rutas de Aprendizaje -->
            @if($student->learningPaths && $student->learningPaths->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-route"></i> Rutas de Aprendizaje</h5>
                </div>
                <div class="card-body">
                    @foreach($student->learningPaths as $path)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $path->title ?? $path->name ?? 'Ruta sin título' }}</h6>
                                    <p class="text-muted small mb-2">{{ $path->description ?? '' }}</p>
                                    <small class="text-muted">
                                        {{ $path->completed_contents ?? 0 }}/{{ $path->total_contents ?? 0 }} contenidos completados
                                    </small>
                                </div>
                                <div class="text-center">
                                    <div class="h5 text-{{ ($path->progress_percentage ?? 0) >= 100 ? 'success' : 'primary' }}">
                                        {{ number_format($path->progress_percentage ?? 0, 1) }}%
                                    </div>
                                    <span class="badge bg-{{ ($path->status ?? 'inactive') == 'active' ? 'primary' : (($path->status ?? 'inactive') == 'completed' ? 'success' : 'secondary') }}">
                                        {{ ucfirst($path->status ?? 'inactive') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Alertas de Riesgo -->
    @if($student->riskAlerts && $student->riskAlerts->where('is_resolved', false)->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Alertas de Riesgo Activas</h5>
                </div>
                <div class="card-body">
                    @foreach($student->riskAlerts->where('is_resolved', false) as $alert)
                        <div class="alert alert-{{ ($alert->severity ?? 'info') == 'critical' ? 'danger' : (($alert->severity ?? 'info') == 'high' ? 'warning' : 'info') }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="alert-heading">{{ $alert->title ?? 'Alerta de riesgo' }}</h6>
                                    <p class="mb-0">{{ $alert->description ?? '' }}</p>
                                    <small class="text-muted">
                                        Creada: 
                                        @if($alert->created_at)
                                            {{ $alert->created_at->format('d/m/Y H:i') }}
                                        @else
                                            N/A
                                        @endif
                                    </small>
                                </div>
                                <span class="badge bg-{{ ($alert->severity ?? 'info') == 'critical' ? 'danger' : (($alert->severity ?? 'info') == 'high' ? 'warning' : 'info') }}">
                                    {{ strtoupper($alert->severity ?? 'INFO') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recomendaciones -->
    @if($student->recommendations && $student->recommendations->where('is_completed', false)->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Recomendaciones Activas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($student->recommendations->where('is_completed', false)->take(6) as $recommendation)
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $recommendation->title ?? 'Recomendación' }}</h6>
                                        <p class="card-text small">{{ $recommendation->description ?? $recommendation->reason ?? '' }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-{{ ($recommendation->priority ?? 'medium') == 'high' ? 'danger' : (($recommendation->priority ?? 'medium') == 'medium' ? 'warning' : 'info') }}">
                                                {{ ucfirst($recommendation->priority ?? 'Media') }}
                                            </span>
                                            <small class="text-muted">
                                                @if(isset($recommendation->created_at))
                                                    {{ \Carbon\Carbon::parse($recommendation->created_at)->format('d/m/Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection