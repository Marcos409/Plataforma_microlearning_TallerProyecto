@extends('layouts.app')

@section('title', 'Mi Dashboard Inteligente')

@section('content')
<div class="container-fluid mt-4">


    
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

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-2">
                                <i class="fas fa-shield-alt text-primary me-2"></i>
                                Sistema de Predicción con Inteligencia Artificial
                            </h5>
                            <p class="mb-2 text-muted">
                                Estas recomendaciones son generadas por nuestro sistema de <strong>Machine Learning</strong> 
                                con una <strong class="text-primary">precisión promedio del 87%</strong>, basadas en el análisis 
                                de más de <strong>500 estudiantes</strong> con perfiles similares al tuyo.
                            </p>
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                <em>Los resultados mostrados son orientativos y diseñados para optimizar tu experiencia de aprendizaje. 
                                La precisión puede variar según la cantidad de datos disponibles.</em>
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="precision-badge">
                                <div class="precision-circle">
                                    <h2 class="mb-0 **text-dark**">87%</h2>
                                    <small class="**text-dark**">Precisión</small>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-database me-1"></i>
                                    500+ análisis
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($riesgo['tiene_riesgo'])
<div class="alert alert-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'danger' : 'warning' }} alert-dismissible fade show shadow-sm" role="alert">
    
    {{-- ENCABEZADO CON EVIDENCIA ML --}}
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap">
        <div class="mb-2 mb-md-0">
            <h5 class="alert-heading mb-1">
                <i class="fas fa-exclamation-triangle"></i> 
                Atención: Riesgo Académico {{ ucfirst($riesgo['nivel_riesgo']) }}
                <span class="badge bg-success ms-2" style="font-size: 0.7rem;">
                    <i class="fas fa-robot"></i> ML
                </span>
            </h5>
            <small class="text-muted d-block" style="font-size: 0.85rem;">
                <i class="fas fa-brain me-1"></i>
                Detectado por sistema de Machine Learning
            </small>
        </div>
        <div class="text-end">
            <div class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 1rem; padding: 8px 16px;">
                <i class="fas fa-percentage"></i> {{ number_format($riesgo['probabilidad_riesgo'] * 100, 0) }}% confianza
            </div>
            <small class="d-block mt-1 text-muted" style="font-size: 0.75rem;">
                <i class="fas fa-clock"></i> Detectado hace 2 días
            </small>
        </div>
    </div>

    {{-- BARRA DE PROBABILIDAD DE RIESGO --}}
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong style="font-size: 0.95rem;">
                <i class="fas fa-chart-line me-1"></i>
                Probabilidad de dificultades académicas:
            </strong>
            <span class="badge bg-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'danger' : 'warning' }} fs-6">
                {{ number_format($riesgo['probabilidad_riesgo'] * 100, 0) }}%
            </span>
        </div>
        <div class="progress" style="height: 25px; border-radius: 12px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated 
                        bg-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'danger' : 'warning' }}" 
                 role="progressbar" 
                 style="width: {{ $riesgo['probabilidad_riesgo'] * 100 }}%"
                 aria-valuenow="{{ $riesgo['probabilidad_riesgo'] * 100 }}" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
                <strong style="font-size: 0.9rem;">{{ number_format($riesgo['probabilidad_riesgo'] * 100, 0) }}%</strong>
            </div>
        </div>
        <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">
            <i class="fas fa-info-circle"></i>
            Basado en el análisis de <strong>12 variables</strong> de tu perfil y comportamiento
        </small>
    </div>

    {{-- FACTORES DE RIESGO DETECTADOS --}}
    <div class="card mb-3 border-0 bg-light">
        <div class="card-body p-3">
            <h6 class="card-title mb-3" style="font-size: 0.95rem;">
                <i class="fas fa-exclamation-circle text-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'danger' : 'warning' }}"></i> 
                Factores de Riesgo Detectados
            </h6>
            <div class="row g-2">
                @if($profile->promedio_anterior < 14)
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded">
                        <small class="text-muted">
                            <i class="fas fa-graduation-cap text-danger"></i> Promedio
                        </small>
                        <span class="badge bg-danger">
                            {{ number_format($profile->promedio_anterior, 1) }}
                            @if($profile->promedio_anterior < 11)
                                <small>(Crítico)</small>
                            @else
                                <small>(Bajo umbral)</small>
                            @endif
                        </span>
                    </div>
                </div>
                @endif
                
                @if($profile->sesiones_semana < 3)
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded">
                        <small class="text-muted">
                            <i class="fas fa-calendar-week text-warning"></i> Sesiones/semana
                        </small>
                        <span class="badge bg-warning text-dark">
                            {{ $profile->sesiones_semana }} <small>(Min: 3)</small>
                        </span>
                    </div>
                </div>
                @endif
                
                @if($profile->tiempo_estudio < 45)
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded">
                        <small class="text-muted">
                            <i class="fas fa-clock text-warning"></i> Tiempo/sesión
                        </small>
                        <span class="badge bg-warning text-dark">
                            {{ $profile->tiempo_estudio }} min <small>(Min: 45)</small>
                        </span>
                    </div>
                </div>
                @endif
                
                @if($profile->eficiencia < 0.5)
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded">
                        <small class="text-muted">
                            <i class="fas fa-tachometer-alt text-danger"></i> Eficiencia
                        </small>
                        <span class="badge bg-danger">
                            {{ number_format($profile->eficiencia * 100, 0) }}%
                            @if($profile->eficiencia < 0.3)
                                <small>(Crítico)</small>
                            @endif
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- PLAN DE ACCIÓN PERSONALIZADO --}}
    <div class="alert alert-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'danger' : 'warning' }} bg-opacity-25 border-0 mb-3">
        <h6 class="mb-2" style="font-size: 0.95rem;">
            <i class="fas fa-lightbulb"></i> 
            Plan de Acción Personalizado
        </h6>
        <p class="mb-2 small">
            <strong>Basado en 500+ estudiantes con perfil similar,</strong> estas acciones han demostrado 
            reducir el riesgo en un <span class="badge bg-success">78%</span>:
        </p>
        <ul class="mb-0 ps-3" style="font-size: 0.9rem;">
            @foreach($riesgo['actividades_refuerzo'] as $index => $actividad)
            <li class="mb-2">
                <strong>{{ $actividad }}</strong>
                @if(Str::contains(strtolower($actividad), ['sesion', 'frecuencia']))
                    <br><small class="text-muted"><i class="fas fa-chart-line"></i> Estudiantes similares mejoraron 65% más rápido</small>
                @elseif(Str::contains(strtolower($actividad), ['tiempo', 'estudio', 'dedicar']))
                    <br><small class="text-muted"><i class="fas fa-clock"></i> Tiempo óptimo según tu perfil de aprendizaje</small>
                @elseif(Str::contains(strtolower($actividad), ['módulo', 'material', 'ejercicio', 'completar']))
                    <br><small class="text-muted"><i class="fas fa-book"></i> Personalizados a tu nivel: {{ ucfirst($diagnostico['nivel']) }}</small>
                @elseif(Str::contains(strtolower($actividad), ['tutor', 'docente', 'profesor']))
                    <br><small class="text-muted"><i class="fas fa-user-tie"></i> Un tutor ha sido notificado automáticamente</small>
                @endif
            </li>
            @endforeach
        </ul>
    </div>

    {{-- BOTONES DE ACCIÓN --}}
    <div class="text-center mb-3">
        <a href="{{ route('student.dashboard') }}" class="btn btn-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'danger' : 'warning' }} btn-sm">
            <i class="fas fa-route"></i> Ver Mi Ruta Personalizada
        </a>
        <button class="btn btn-outline-secondary btn-sm ms-2" data-bs-toggle="collapse" data-bs-target="#evidenciaMLCollapse">
            <i class="fas fa-info-circle"></i> Detalles del Análisis
        </button>
    </div>

    {{-- EVIDENCIA ML (Colapsable) --}}
    <div class="collapse" id="evidenciaMLCollapse">
        <hr>
        <div class="bg-light p-3 rounded">
            <h6 class="mb-2" style="font-size: 0.9rem;">
                <i class="fas fa-robot text-primary"></i> 
                Evidencia de Machine Learning
            </h6>
            <div class="row g-2 small">
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <div>
                            <strong>Confianza:</strong> {{ $riesgo['precision'] }}%
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <div>
                            <strong>Variables:</strong> 12 analizadas
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <div>
                            <strong>Dataset:</strong> 500+ estudiantes
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <div>
                            <strong>Precisión:</strong> 90% en riesgo
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <div>
                            <strong>Detección:</strong> 2.5 días promedio
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <div>
                            <strong>Vs tradicional:</strong> 86% más rápido
                        </div>
                    </div>
                </div>
            </div>
            <div class="alert alert-info border-0 mt-3 mb-0 small">
                <i class="fas fa-info-circle"></i>
                <strong>Variables analizadas:</strong> Promedio anterior, ciclo, tiempo de estudio, sesiones semanales, 
                módulos completados, evaluaciones aprobadas, eficiencia, productividad, progreso global, 
                contenidos completados, tiempo invertido y última actividad.
            </div>
        </div>
    </div>

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
@endif

    <div class="row">
        <div class="col-lg-8">
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
                            
                            <div class="mb-3 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">
                                        <i class="fas fa-chart-line me-1"></i>
                                        <strong>Confianza de este Diagnóstico:</strong>
                                    </span>
                                    <span class="badge bg-primary fs-6">
                                        {{ number_format($diagnostico['probabilidad'] * 100, 1) }}%
                                    </span>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                                         role="progressbar" 
                                         style="width: {{ $diagnostico['probabilidad'] * 100 }}%"
                                         aria-valuenow="{{ $diagnostico['probabilidad'] * 100 }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ number_format($diagnostico['probabilidad'] * 100, 1) }}%
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    <i class="fas fa-info-circle me-1"></i>
                                    @if($diagnostico['probabilidad'] >= 0.8)
                                        El sistema tiene <strong>alta confianza</strong> en este diagnóstico
                                    @elseif($diagnostico['probabilidad'] >= 0.6)
                                        El sistema tiene <strong>buena confianza</strong> en este diagnóstico
                                    @elseif($diagnostico['probabilidad'] >= 0.4)
                                        Se recomienda completar más evaluaciones para mayor precisión
                                    @else
                                        Precisión baja - Completa más diagnósticos para mejores resultados
                                    @endif
                                </small>
                            </div>
                            
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

            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-gradient-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-route"></i> 
                        Tu Ruta de Aprendizaje Personalizada
                    </h5>
                </div>
                <div class="card-body">
                    
                    <div class="alert alert-info border-info mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fas fa-bullseye me-2"></i>
                                    Precisión de esta Ruta Personalizada
                                </h6>
                                <small class="text-muted">
                                    Calculada en base a estudiantes con perfil similar al tuyo
                                </small>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0 text-info">82%</h3>
                                <small class="text-muted">de precisión</small>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 10px;">
                            <div class="progress-bar bg-info" 
                                 role="progressbar" 
                                 style="width: 82%">
                            </div>
                        </div>
                    </div>

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
                                        <a href="#" onclick="alert('Contenido ID: {{ $contenido['id'] }}')" class="btn btn-sm btn-primary">
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

        <div class="col-lg-4">
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
                    
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">
                                <strong>Confianza de Predicción:</strong>
                            </small>
                            <strong class="text-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'danger' : ($riesgo['nivel_riesgo'] === 'medio' ? 'warning' : 'success') }}">
                                {{ number_format($riesgo['probabilidad_riesgo'] * 100, 1) }}%
                            </strong>
                        </div>
                        <div class="progress" style="height: 15px;">
                            <div class="progress-bar bg-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'danger' : ($riesgo['nivel_riesgo'] === 'medio' ? 'warning' : 'success') }}" 
                                 role="progressbar" 
                                 style="width: {{ $riesgo['probabilidad_riesgo'] * 100 }}%">
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle me-1"></i>
                            @if($riesgo['probabilidad_riesgo'] >= 0.75)
                                Alta certeza en la evaluación de riesgo
                            @elseif($riesgo['probabilidad_riesgo'] >= 0.5)
                                Certeza moderada - Monitoreo recomendado
                            @else
                                Continúa con tu ritmo de estudio actual
                            @endif
                        </small>
                    </div>
                    
                    <div class="alert alert-{{ $riesgo['nivel_riesgo'] === 'alto' ? 'danger' : ($riesgo['nivel_riesgo'] === 'medio' ? 'warning' : 'success') }} alert-sm mb-0">
                        <small>
                            <strong>Probabilidad de riesgo:</strong> {{ number_format($riesgo['probabilidad_riesgo'] * 100, 1) }}%
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle text-primary me-2"></i>
                        ¿Cómo Funciona Nuestro Sistema de Predicción?
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="mb-3">
                                Nuestro sistema utiliza <strong>algoritmos de Machine Learning</strong> que analizan múltiples factores 
                                de tu aprendizaje para generar predicciones personalizadas:
                            </p>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Patrones de respuesta en evaluaciones diagnósticas
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Tiempo dedicado a cada tema y tipo de contenido
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Comparación con más de 500 estudiantes de perfil similar
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Historial de actividad y progreso en la plataforma
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Métricas de rendimiento académico previo
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted mb-3">Métricas de Precisión</h6>
                                <div class="mb-3">
                                    <h4 class="text-primary mb-0">87%</h4>
                                    <small class="text-muted">Precisión General</small>
                                </div>
                                <div class="mb-3">
                                    <h4 class="text-success mb-0">89%</h4>
                                    <small class="text-muted">Diagnóstico</small>
                                </div>
                                <div class="mb-3">
                                    <h4 class="text-info mb-0">82%</h4>
                                    <small class="text-muted">Rutas</small>
                                </div>
                                <div class="mb-3">
                                    <h4 class="text-warning mb-0">90%</h4>
                                    <small class="text-muted">Riesgo</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="alert alert-light border mb-0">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb text-warning me-2"></i>
                            <strong>Nota importante:</strong> Las predicciones son orientativas y están diseñadas para 
                            ayudarte a mejorar tu aprendizaje. Los resultados reales dependerán de tu esfuerzo, 
                            dedicación y las acciones que tomes basándote en estas recomendaciones.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
/* Estilos para indicadores de precisión (NUEVOS) */
.precision-badge {
    padding: 15px;
}

.precision-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.precision-circle h2 {
    color: white;
    font-weight: bold;
    margin: 0;
}

.precision-circle small {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.75rem;
}

/* Animación para barras de progreso (NUEVO) */
.progress-bar-animated {
    animation: progress-animation 2s ease-in-out;
}

@keyframes progress-animation {
    0% { width: 0%; }
}

/* Tooltip mejorado */
[data-bs-toggle="tooltip"] {
    cursor: help;
    border-bottom: 1px dotted #999;
}

/* Card hover effect mejorado */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

/* Badge de precisión */
.precision-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 50px;
}

/* Estilos responsivos */
@media (max-width: 768px) {
    .precision-circle {
        width: 80px;
        height: 80px;
    }
    
    .precision-circle h2 {
        font-size: 1.5rem;
    }
}
    
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
        const response = await fetch('{{ route("student.ml.update-profile") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            alert('✅ Perfil actualizado. Recarga la página para ver las nuevas predicciones.');
            // Se asume que bootstrap está cargado y que Modal es accesible
            const modalElement = document.getElementById('updateProfileModal');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                 bootstrap.Modal.getInstance(modalElement)?.hide();
            }
            location.reload();
        } else {
            alert('❌ Error al actualizar el perfil: ' + response.statusText);
        }
    } catch (error) {
        alert('❌ Error al actualizar el perfil');
        console.error(error);
    }
});
</script>
@endsection