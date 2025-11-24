@extends('layouts.app')
@section('title', 'Resultados de Análisis ML - Predicción de Dificultades')
@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="fas fa-brain text-primary me-2"></i>
                Predicción de Dificultades de Aprendizaje
            </h1>
            <p class="text-muted">Sistema de análisis predictivo con Machine Learning</p>
        </div>
        <div class="col-auto">
            <form action="{{ route('admin.ml.analyzeAll') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-robot me-2"></i>Analizar Todos los Estudiantes
                </button>
            </form>
        </div>
    </div>

    <!-- ⭐ MEJORA 1: Estadísticas más detalladas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Analizados</h6>
                            <h2 class="mb-0 text-primary">{{ $statistics['total'] }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Nivel Avanzado</h6>
                            <h2 class="mb-0 text-success">{{ $statistics['diagnostico_avanzado'] }}</h2>
                            <small class="text-muted">Sin dificultades</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Riesgo Medio</h6>
                            <h2 class="mb-0 text-warning">{{ $statistics['riesgo_medio'] }}</h2>
                            <small class="text-muted">Requiere seguimiento</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Riesgo Alto</h6>
                            <h2 class="mb-0 text-danger">{{ $statistics['riesgo_alto'] }}</h2>
                            <small class="text-muted">Intervención urgente</small>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ⭐ MEJORA 2: Filtros para evidenciar mejor -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.ml.results') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Filtrar por nivel de riesgo:</label>
                    <select name="riesgo" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="alto" {{ request('riesgo') == 'alto' ? 'selected' : '' }}>Alto</option>
                        <option value="medio" {{ request('riesgo') == 'medio' ? 'selected' : '' }}>Medio</option>
                        <option value="bajo" {{ request('riesgo') == 'bajo' ? 'selected' : '' }}>Bajo</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filtrar por diagnóstico:</label>
                    <select name="diagnostico" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="deficiente" {{ request('diagnostico') == 'deficiente' ? 'selected' : '' }}>Deficiente</option>
                        <option value="intermedio" {{ request('diagnostico') == 'intermedio' ? 'selected' : '' }}>Intermedio</option>
                        <option value="avanzado" {{ request('diagnostico') == 'avanzado' ? 'selected' : '' }}>Avanzado</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de resultados mejorada -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Análisis Predictivo por Estudiante
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Estudiante</th>
                            <th>Código</th>
                            <th>Carrera</th>
                            <th>Diagnóstico</th>
                            <th>Ruta Sugerida</th>
                            <th>Nivel de Riesgo</th>
                            <th>Fecha Análisis</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analyses as $analysis)
                        <tr class="{{ $analysis->nivel_riesgo === 'alto' ? 'table-danger' : ($analysis->nivel_riesgo === 'medio' ? 'table-warning' : '') }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <strong>{{ $analysis->student_name }}</strong>
                                </div>
                            </td>
                            <td><code>{{ $analysis->student_code }}</code></td>
                            <td><small class="text-muted">{{ $analysis->student_career }}</small></td>
                            <td>
                                <span class="badge bg-{{ $analysis->diagnostico === 'avanzado' ? 'success' : ($analysis->diagnostico === 'intermedio' ? 'info' : 'danger') }}">
                                    <i class="fas fa-{{ $analysis->diagnostico === 'avanzado' ? 'check' : 'exclamation' }} me-1"></i>
                                    {{ ucfirst($analysis->diagnostico) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ str_replace('_', ' ', ucfirst($analysis->ruta_aprendizaje)) }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $analysis->nivel_riesgo === 'bajo' ? 'success' : ($analysis->nivel_riesgo === 'medio' ? 'warning' : 'danger') }}">
                                    <i class="fas fa-{{ $analysis->nivel_riesgo === 'bajo' ? 'shield-alt' : 'exclamation-triangle' }} me-1"></i>
                                    Riesgo {{ ucfirst($analysis->nivel_riesgo) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="far fa-calendar me-1"></i>
                                    {{ $analysis->created_at->format('d/m/Y') }}
                                    <br>
                                    <i class="far fa-clock me-1"></i>
                                    {{ $analysis->created_at->format('H:i') }}
                                </small>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal{{ $analysis->id }}">
                                    <i class="fas fa-eye me-1"></i> Ver Detalles
                                </button>
                            </td>
                        </tr>

                        <!-- ⭐ MEJORA 3: Modal completamente mejorado con todas las secciones -->
                        <div class="modal fade" id="detailModal{{ $analysis->id }}" tabindex="-1">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-brain me-2"></i>
                                            Análisis Predictivo: {{ $analysis->student_name }}
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Información del estudiante -->
                                        <div class="alert alert-info mb-4">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <strong><i class="fas fa-user me-2"></i>Estudiante:</strong> {{ $analysis->student_name }}
                                                </div>
                                                <div class="col-md-4">
                                                    <strong><i class="fas fa-id-card me-2"></i>Código:</strong> {{ $analysis->student_code }}
                                                </div>
                                                <div class="col-md-4">
                                                    <strong><i class="fas fa-graduation-cap me-2"></i>Carrera:</strong> {{ $analysis->student_career }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ⭐ SECCIÓN 1: Métricas ML -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-chart-bar me-2 text-primary"></i>
                                                    Métricas de Predicción ML
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach($analysis->metricas as $key => $value)
                                                    <div class="col-md-6 mb-3">
                                                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                                            <div>
                                                                <strong class="text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}</strong>
                                                                <i class="fas fa-info-circle text-muted ms-1" 
                                                                   data-bs-toggle="tooltip" 
                                                                   title="@if($key == 'probabilidad_diagnostico') Probabilidad de éxito del estudiante @elseif($key == 'probabilidad_riesgo') Probabilidad de abandono o bajo rendimiento @else Indicador predictivo @endif"></i>
                                                            </div>
                                                            <span class="badge bg-{{ is_numeric($value) && $value < 0.5 ? 'danger' : 'success' }} fs-6">
                                                                {{ is_numeric($value) ? round($value * 100, 2) . '%' : $value }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ⭐ SECCIÓN 2: DIFICULTADES DETECTADAS (CRÍTICO) -->
                                        @if(!empty($analysis->recomendaciones['temas_problematicos']))
                                        <div class="card mb-3 border-danger">
                                            <div class="card-header bg-danger text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    🎯 Dificultades de Aprendizaje Detectadas
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted mb-3">
                                                    <strong>El sistema identificó las siguientes áreas problemáticas:</strong>
                                                </p>
                                                <div class="list-group">
                                                    @foreach($analysis->recomendaciones['temas_problematicos'] as $tema)
                                                    <div class="list-group-item list-group-item-danger">
                                                        <i class="fas fa-times-circle me-2"></i>
                                                        <strong>{{ $tema }}</strong>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @else
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            No se detectaron dificultades significativas en este momento.
                                        </div>
                                        @endif

                                        <!-- ⭐ SECCIÓN 3: Contenido Recomendado -->
                                        @if(!empty($analysis->recomendaciones['contenido']))
                                        <div class="card mb-3 border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-book me-2"></i>
                                                    📚 Contenido de Microlearning Recomendado
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted mb-3">
                                                    Contenidos personalizados sugeridos por el sistema ML:
                                                </p>
                                                <div class="list-group">
                                                    @foreach($analysis->recomendaciones['contenido'] as $contenido)
                                                    <div class="list-group-item">
                                                        <i class="fas fa-video text-primary me-2"></i>
                                                        {{ $contenido }}
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- ⭐ SECCIÓN 4: Actividades de Refuerzo -->
                                        @if(!empty($analysis->recomendaciones['actividades_refuerzo']))
                                        <div class="card mb-3 border-warning">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-tasks me-2"></i>
                                                    ✅ Plan de Actividades de Refuerzo
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($analysis->recomendaciones['actividades_refuerzo'] as $actividad)
                                                    <li class="mb-2">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        {{ $actividad }}
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- ⭐ SECCIÓN 5: Ruta de Aprendizaje Personalizada -->
                                        @if(!empty($analysis->recomendaciones['ruta_pasos']))
                                        <div class="card mb-3 border-info">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-route me-2"></i>
                                                    🗺️ Ruta de Aprendizaje Personalizada
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted mb-3">
                                                    <strong>Tipo de ruta:</strong> {{ str_replace('_', ' ', ucfirst($analysis->ruta_aprendizaje)) }}
                                                </p>
                                                <ol class="mb-0">
                                                    @foreach($analysis->recomendaciones['ruta_pasos'] as $index => $paso)
                                                    <li class="mb-3">
                                                        <strong>{{ $paso['contenido'] }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <span class="badge bg-secondary">{{ $paso['tipo'] }}</span>
                                                        </small>
                                                    </li>
                                                    @endforeach
                                                </ol>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- ⭐ SECCIÓN 6: Acciones Automáticas del Sistema -->
                                        <div class="card border-success">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-cogs me-2"></i>
                                                    🤖 Acciones Automáticas Ejecutadas
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-unstyled mb-0">
                                                    <li class="mb-2">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        Análisis ML completado el {{ $analysis->created_at->format('d/m/Y H:i') }}
                                                    </li>
                                                    @if($analysis->nivel_riesgo === 'alto' || $analysis->nivel_riesgo === 'medio')
                                                    <li class="mb-2">
                                                        <i class="fas fa-bell text-warning me-2"></i>
                                                        Alerta de riesgo generada automáticamente
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="fas fa-envelope text-info me-2"></i>
                                                        Notificación enviada al tutor/docente asignado
                                                    </li>
                                                    @endif
                                                    <li class="mb-2">
                                                        <i class="fas fa-book-open text-primary me-2"></i>
                                                        Contenido de refuerzo asignado al perfil del estudiante
                                                    </li>
                                                    <li>
                                                        <i class="fas fa-calendar-check text-success me-2"></i>
                                                        Seguimiento programado automáticamente
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        <a href="{{ route('admin.students.show', $analysis->user_id) }}" class="btn btn-primary">
                                            <i class="fas fa-user me-1"></i> Ver Perfil Completo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                                    <p class="mb-0">No hay análisis disponibles.</p>
                                    <p class="small">Haz clic en "Analizar Todos los Estudiantes" para comenzar el análisis predictivo.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-3">
                {{ $analyses->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Script para tooltips -->
@push('scripts')
<script>
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
@endpush
@endsection