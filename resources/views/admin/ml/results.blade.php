@extends('layouts.app')

@section('title', 'Resultados de Análisis ML')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Resultados de Análisis con IA</h1>
            <p class="text-muted">Análisis realizados por Machine Learning</p>
        </div>
        <div class="col-auto">
            <form action="{{ route('admin.ml.analyzeAll') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-robot me-2"></i>Analizar Todos
                </button>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Análisis</h5>
                    <h2>{{ $statistics['total'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Nivel Avanzado</h5>
                    <h2>{{ $statistics['diagnostico_avanzado'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Riesgo Medio</h5>
                    <h2>{{ $statistics['riesgo_medio'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Riesgo Alto</h5>
                    <h2>{{ $statistics['riesgo_alto'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Análisis por Estudiante</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Código</th>
                            <th>Carrera</th>
                            <th>Diagnóstico</th>
                            <th>Ruta</th>
                            <th>Riesgo</th>
                            <th>Fecha Análisis</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analyses as $analysis)
                        <tr>
                            <td>{{ $analysis->user->name }}</td>
                            <td>{{ $analysis->user->student_code }}</td>
                            <td>{{ $analysis->user->career }}</td>
                            <td>
                                <span class="badge bg-{{ $analysis->diagnostico === 'avanzado' ? 'success' : ($analysis->diagnostico === 'intermedio' ? 'info' : 'warning') }}">
                                    {{ ucfirst($analysis->diagnostico) }}
                                </span>
                            </td>
                            <td>{{ str_replace('_', ' ', ucfirst($analysis->ruta_aprendizaje)) }}</td>
                            <td>
                                <span class="badge bg-{{ $analysis->nivel_riesgo === 'bajo' ? 'success' : ($analysis->nivel_riesgo === 'medio' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($analysis->nivel_riesgo) }}
                                </span>
                            </td>
                            <td>{{ $analysis->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal{{ $analysis->id }}">
                                    <i class="fas fa-eye"></i> Ver Detalles
                                </button>
                            </td>
                        </tr>

                        <!-- Modal de detalles -->
                        <div class="modal fade" id="detailModal{{ $analysis->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Análisis de {{ $analysis->user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <h6 class="mb-3">Métricas</h6>
                                        <ul class="list-group mb-3">
                                            @foreach($analysis->metricas as $key => $value)
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                <span>{{ is_numeric($value) ? round($value * 100, 2) . '%' : $value }}</span>
                                            </li>
                                            @endforeach
                                        </ul>

                                        <h6 class="mb-3">Recomendaciones</h6>
                                        @if(!empty($analysis->recomendaciones['actividades_refuerzo']))
                                        <div class="alert alert-warning">
                                            <strong>Actividades de Refuerzo:</strong>
                                            <ul class="mb-0 mt-2">
                                                @foreach($analysis->recomendaciones['actividades_refuerzo'] as $actividad)
                                                <li>{{ $actividad }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endif

                                        @if(!empty($analysis->recomendaciones['ruta_pasos']))
                                        <div class="alert alert-info">
                                            <strong>Ruta de Aprendizaje:</strong>
                                            <ol class="mb-0 mt-2">
                                                @foreach($analysis->recomendaciones['ruta_pasos'] as $paso)
                                                <li>{{ $paso['contenido'] }} ({{ $paso['tipo'] }})</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <p class="text-muted mb-0">No hay análisis disponibles. Haz clic en "Analizar Todos" para comenzar.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $analyses->links() }}
        </div>
    </div>
</div>
@endsection