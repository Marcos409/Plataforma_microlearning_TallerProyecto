@extends('layouts.app')

@section('title', 'Mi Dashboard Inteligente')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-1">
                <i class="fas fa-brain text-primary"></i> 
                Dashboard Inteligente
            </h1>
            <p class="text-muted">
                Recomendaciones personalizadas basadas en tu rendimiento e inteligencia artificial
            </p>
        </div>
    </div>

    <!-- Alerta de Riesgo (si aplica) -->
    @if($riesgo['tiene_riesgo'])
    <div class="alert alert-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'danger' : 'warning' }} alert-dismissible fade show" role="alert">
        <h5 class="alert-heading">
            <i class="fas fa-exclamation-triangle"></i> 
            Atención: Riesgo Académico {{ ucfirst($riesgo['nivel_riesgo']) }}
        </h5>
        <p class="mb-2">Hemos detectado que necesitas refuerzo en algunas áreas. Te recomendamos:</p>
        <ul class="mb-0">
            @foreach($riesgo['actividades_refuerzo'] as $actividad)
            <li>{{ $actividad }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Columna Izquierda: Diagnóstico y Progreso -->
        <div class="col-lg-8">
            <!-- Card de Diagnóstico -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-stethoscope"></i> 
                        Tu Diagnóstico de Aprendizaje
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <div class="nivel-badge nivel-{{ $diagnostico['nivel'] }}">
                                <i class="fas fa-award fa-3x mb-2"></i>
                                <h3 class="mb-0">{{ ucfirst($diagnostico['nivel']) }}</h3>
                                <small class="text-muted">Tu Nivel Actual</small>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="fw-bold">Análisis de tu rendimiento:</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Probabilidad de avance</span>
                                    <span class="fw-bold">{{ number_format($diagnostico['probabilidad'] * 100, 1) }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $diagnostico['probabilidad'] * 100 }}%"></div>
                                </div>
                            </div>

                            @if(!empty($diagnostico['temas_problematicos']))
                            <div class="alert alert-warning py-2 mb-2">
                                <small><strong>Áreas de mejora:</strong></small>
                                <div class="mt-1">
                                    @foreach($diagnostico['temas_problematicos'] as $tema)
                                    <span class="badge bg-warning text-dark me-1">{{ $tema }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if(!empty($diagnostico['contenido_recomendado']))
                            <div class="mt-2">
                                <small class="text-muted d-block mb-1"><i class="fas fa-lightbulb"></i> Te recomendamos:</small>
                                @foreach($diagnostico['contenido_recomendado'] as $contenido)
                                <span class="badge bg-info me-1">{{ $contenido }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card de Ruta de Aprendizaje -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-gradient-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-route"></i> 
                        Tu Ruta de Aprendizaje Personalizada
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h6 class="text-muted mb-1">Tipo de Ruta</h6>
                                <h5 class="mb-0 text-capitalize">
                                    @if($ruta['tipo_ruta'] === 'refuerzo_basico')
                                        <i class="fas fa-book text-info"></i> Refuerzo Básico
                                    @elseif($ruta['tipo_ruta'] === 'practica_intensiva')
                                        <i class="fas fa-dumbbell text-warning"></i> Práctica Intensiva
                                    @else
                                        <i class="fas fa-rocket text-success"></i> Avance Normal
                                    @endif
                                </h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h6 class="text-muted mb-1">Progreso Esperado</h6>
                                <h5 class="mb-0">
                                    <span class="badge bg-{{ $ruta['progreso_esperado'] === 'alto' ? 'success' : 'warning' }}">
                                        {{ ucfirst($ruta['progreso_esperado']) }}
                                    </span>
                                </h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h6 class="text-muted mb-1">Dificultad</h6>
                                <h5 class="mb-0">
                                    <span class="badge bg-primary">
                                        {{ ucfirst($ruta['dificultad_recomendada']) }}
                                    </span>
                                </h5>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Pasos recomendados:</h6>
                    <div class="timeline">
                        @foreach($ruta['ruta_aprendizaje'] as $paso)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary">
                                <i class="fas fa-{{ $paso['tipo'] === 'video' ? 'play' : ($paso['tipo'] === 'quiz' ? 'question' : 'book') }}"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Paso {{ $paso['paso'] }}: {{ $paso['contenido'] }}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-tag"></i> {{ ucfirst($paso['tipo']) }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Card de Contenido Recomendado -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-gradient-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-stars"></i> 
                        Contenido Recomendado para Ti
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($recomendaciones) > 0)
                    <div class="row">
                        @foreach($recomendaciones as $contenido)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">{{ $contenido['title'] }}</h6>
                                        <span class="badge bg-{{ $contenido['difficulty_level'] === 'basico' ? 'success' : ($contenido['difficulty_level'] === 'intermedio' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($contenido['difficulty_level']) }}
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-2">{{ Str::limit($contenido['description'], 80) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> {{ $contenido['estimated_duration'] }} min
                                        </small>
                                        <a href="{{ route('content.show', $contenido['id']) }}" class="btn btn-sm btn-primary">
                                            Ver ahora <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                    @if($contenido['progress'] > 0)
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar" style="width: {{ $contenido['progress'] }}%"></div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Completa más contenido para recibir recomendaciones personalizadas</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Estadísticas y Perfil -->
        <div class="col-lg-4">
            <!-- Card de Progreso -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-chart-line"></i> Tu Progreso</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="circular-progress mx-auto" style="width: 120px; height: 120px;">
                            <svg viewBox="0 0 36 36" class="circular-chart">
                                <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="circle" stroke-dasharray="{{ $progreso['completion_rate'] }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <text x="18" y="20.35" class="percentage">{{ $progreso['completion_rate'] }}%</text>
                            </svg>
                        </div>
                        <p class="text-muted small mt-2">Completado</p>
                    </div>
                    <div class="row text-center g-2">
                        <div class="col-6">
                            <div class="p-2 bg-success bg-opacity-10 rounded">
                                <h5 class="mb-0 text-success">{{ $progreso['completed'] }}</h5>
                                <small class="text-muted">Completados</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-warning bg-opacity-10 rounded">
                                <h5 class="mb-0 text-warning">{{ $progreso['in_progress'] }}</h5>
                                <small class="text-muted">En Progreso</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card de Perfil de Estudiante -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-user-graduate"></i> Tu Perfil</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Ciclo</small>
                            <strong>{{ $profile->ciclo }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Módulos completados</small>
                            <strong>{{ $profile->modulos_completados }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Evaluaciones aprobadas</small>
                            <strong>{{ $profile->evaluaciones_aprobadas }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Promedio anterior</small>
                            <strong class="text-{{ $profile->promedio_anterior >= 14 ? 'success' : ($profile->promedio_anterior >= 11 ? 'warning' : 'danger') }}">
                                {{ number_format($profile->promedio_anterior, 1) }}
                            </strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Tiempo de estudio</small>
                            <strong>{{ $profile->tiempo_estudio }} min/sesión</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Sesiones semanales</small>
                            <strong>{{ $profile->sesiones_semana }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Eficiencia</small>
                            <strong>{{ number_format($profile->eficiencia * 100, 1) }}%</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Productividad</small>
                            <strong>{{ number_format($profile->productividad * 100, 1) }}%</strong>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#updateProfileModal">
                        <i class="fas fa-edit"></i> Actualizar Perfil
                    </button>
                </div>
            </div>

            <!-- Card de Riesgo -->
            <div class="card shadow-sm border-0 border-start border-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'danger' : ($riesgo['nivel_riesgo'] === 'medio' ? 'warning' : 'success') }} border-4">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-shield-alt"></i> 
                        Estado de Riesgo Académico
                    </h6>
                    <div class="text-center mb-3">
                        <div class="risk-indicator risk-{{ $riesgo['nivel_riesgo'] }}">
                            <i class="fas fa-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'exclamation-triangle' : ($riesgo['nivel_riesgo'] === 'medio' ? 'exclamation-circle' : 'check-circle') }} fa-3x"></i>
                            <h4 class="mt-2 mb-0">{{ ucfirst($riesgo['nivel_riesgo']) }}</h4>
                            <small class="text-muted">Nivel de riesgo</small>
                        </div>
                    </div>
                    <div class="alert alert-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'danger' : ($riesgo['nivel_riesgo'] === 'medio' ? 'warning' : 'success') }} alert-sm mb-0">
                        <small>
                            <strong>Probabilidad:</strong> {{ number_format($riesgo['probabilidad_riesgo'] * 100, 1) }}%
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para actualizar perfil -->
<div class="modal fade" id="updateProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Actualizar Mi Perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateProfileForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ciclo Actual</label>
                        <input type="number" class="form-control" name="ciclo" value="{{ $profile->ciclo }}" min="1" max="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tiempo de Estudio (minutos por sesión)</label>
                        <input type="number" class="form-control" name="tiempo_estudio" value="{{ $profile->tiempo_estudio }}" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sesiones por Semana</label>
                        <input type="number" class="form-control" name="sesiones_semana" value="{{ $profile->sesiones_semana }}" min="0" max="7">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.bg-gradient-success { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.bg-gradient-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

.nivel-badge {
    padding: 20px;
    border-radius: 15px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.nivel-basico { border-left: 5px solid #28a745; }
.nivel-intermedio { border-left: 5px solid #ffc107; }
.nivel-avanzado { border-left: 5px solid #dc3545; }

.timeline { position: relative; padding-left: 40px; }
.timeline-item { position: relative; padding-bottom: 20px; }
.timeline-item:before {
    content: '';
    position: absolute;
    left: -27px;
    top: 8px;
    bottom: -12px;
    width: 2px;
    background: #e0e0e0;
}
.timeline-item:last-child:before { display: none; }
.timeline-marker {
    position: absolute;
    left: -40px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.hover-shadow { transition: all 0.3s; }
.hover-shadow:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }

.circular-chart { display: block; margin: 10px auto; max-width: 80%; max-height: 250px; }
.circle-bg { fill: none; stroke: #eee; stroke-width: 3.8; }
.circle { fill: none; stroke-width: 2.8; stroke-linecap: round; animation: progress 1s ease-out forwards; stroke: #667eea; }
@keyframes progress { 0% { stroke-dasharray: 0 100; } }
.percentage { fill: #666; font-family: sans-serif; font-size: 0.5em; text-anchor: middle; }

.risk-indicator { padding: 15px; border-radius: 10px; }
.risk-bajo { background: #d4edda; color: #155724; }
.risk-medio { background: #fff3cd; color: #856404; }
.risk-alto { background: #f8d7da; color: #721c24; }
</style>

<script>
document.getElementById('updateProfileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('{{ route("ml.update-profile") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            alert('✅ Perfil actualizado. Recarga la página para ver las nuevas predicciones.');
            bootstrap.Modal.getInstance(document.getElementById('updateProfileModal')).hide();
            location.reload();
        }
    } catch (error) {
        alert('❌ Error al actualizar el perfil');
    }
});
</script>
@endsection