@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Mi Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-calendar"></i> Hoy: {{ date('d/m/Y') }}
            </button>
        </div>
    </div>
</div>

<!-- Alertas de Riesgo -->
@if($riskAlerts->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-risk" role="alert">
            <h5><i class="fas fa-exclamation-triangle"></i> Alertas Importantes</h5>
            @foreach($riskAlerts as $alert)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>{{ $alert->title }}</strong><br>
                        <small>{{ $alert->description }}</small>
                    </div>
                    <span class="badge bg-{{ $alert->severity == 'critical' ? 'danger' : ($alert->severity == 'high' ? 'warning' : 'info') }}">
                        {{ strtoupper($alert->severity) }}
                    </span>
                </div>
                @if(!$loop->last)<hr class="my-2">@endif
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Cards de Resumen -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75 small">Progreso General</div>
                        <div class="text-lg font-weight-bold">{{ number_format($overallProgress, 1) }}%</div>
                    </div>
                    <div class="progress-circle bg-white bg-opacity-25" style="background: conic-gradient(white {{ $overallProgress * 3.6 }}deg, rgba(255,255,255,0.3) 0deg);">
                        <span class="text-white font-weight-bold">{{ number_format($overallProgress) }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75 small">Rutas Activas</div>
                        <div class="text-lg font-weight-bold">{{ $learningPaths->count() }}</div>
                    </div>
                    <i class="fas fa-route fa-2x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75 small">Recomendaciones</div>
                        <div class="text-lg font-weight-bold">{{ $recommendations->count() }}</div>
                    </div>
                    <i class="fas fa-lightbulb fa-2x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75 small">Alertas</div>
                        <div class="text-lg font-weight-bold">{{ $riskAlerts->count() }}</div>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Rutas de Aprendizaje -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-route text-primary"></i> Mis Rutas de Aprendizaje</h5>
                <a href="{{ route('student.learning-paths.index') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
            </div>
            <div class="card-body">
                @forelse($learningPaths as $path)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-2">{{ $path->name }}</h6>
                                <p class="text-muted small mb-2">{{ $path->description }}</p>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-3" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: {{ $path->progress_percentage }}%"></div>
                                    </div>
                                    <span class="small text-muted">{{ number_format($path->progress_percentage, 1) }}%</span>
                                </div>
                                <small class="text-muted">
                                    {{ $path->completed_contents }}/{{ $path->total_contents }} contenidos completados
                                </small>
                            </div>
                            <div class="ms-3">
                                <a href="{{ route('student.learning-paths.show', $path) }}" class="btn btn-sm btn-primary">
                                    Continuar
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-route fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No tienes rutas de aprendizaje activas.</p>
                        <a href="{{ route('student.diagnostics.index') }}" class="btn btn-primary">
                            Realizar diagnóstico
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recomendaciones -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-lightbulb text-warning"></i> Recomendaciones</h5>
            </div>
            <div class="card-body">
                @forelse($recommendations as $recommendation)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex align-items-start">
                            <div class="me-2">
                                @if($recommendation->content->type == 'video')
                                    <i class="fas fa-play-circle text-danger"></i>
                                @elseif($recommendation->content->type == 'pdf')
                                    <i class="fas fa-file-pdf text-danger"></i>
                                @else
                                    <i class="fas fa-book text-primary"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 small">{{ $recommendation->content->title }}</h6>
                                <p class="text-muted small mb-1">{{ Str::limit($recommendation->reason, 60) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <span class="badge bg-{{ $recommendation->priority == 1 ? 'danger' : ($recommendation->priority == 2 ? 'warning' : 'info') }}">
                                            {{ $recommendation->priority == 1 ? 'Alta' : ($recommendation->priority == 2 ? 'Media' : 'Baja') }}
                                        </span>
                                    </small>
                                    <a href="{{ route('student.content.show', $recommendation->content) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3">
                        <i class="fas fa-lightbulb fa-2x text-muted mb-2"></i>
                        <p class="text-muted small">No hay recomendaciones pendientes.</p>
                    </div>
                @endforelse
                
                @if($recommendations->count() > 0)
                    <div class="text-center mt-3">
                        <a href="{{ route('student.recommendations.index') }}" class="btn btn-sm btn-outline-primary">
                            Ver todas las recomendaciones
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Progreso por Materia -->
@if($subjectProgress->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar text-info"></i> Progreso por Materia</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($subjectProgress as $subject => $progress)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-2">{{ $subject }}</h6>
                                @foreach($progress as $item)
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small">
                                            <span>Progreso</span>
                                            <span>{{ number_format($item->progress_percentage, 1) }}%</span>
                                        </div>
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $item->progress_percentage }}%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between small text-muted">
                                            <span>Promedio</span>
                                            <span>{{ number_format($item->average_score, 1) }}%</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Actividad Reciente -->
@if($recentActivity->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock text-success"></i> Actividad Reciente</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($recentActivity as $activity)
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fas fa-check text-white small"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $activity->content->title }}</h6>
                                <p class="text-muted small mb-1">Completado el {{ $activity->completed_at->format('d/m/Y H:i') }}</p>
                                <small class="text-success">
                                    <i class="fas fa-clock"></i> {{ $activity->time_spent }} minutos
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
// Auto-actualizar última actividad cada 5 minutos
setInterval(function() {
    fetch('{{ route("student.update-activity") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    });
}, 300000); // 5 minutos
</script>
@endsection