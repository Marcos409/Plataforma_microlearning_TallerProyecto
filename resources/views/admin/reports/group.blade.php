{{-- resources/views/admin/reports/group.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0">
                <i class="fas fa-chart-bar"></i> Informe de Grupo
            </h3>
        </div>
        
        <div class="card-body">
            {{-- FILTROS --}}
            <form id="filterForm" method="GET" action="{{ route('admin.reports.group') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Carrera</label>
                        <select name="career" class="form-select" required>
                            <option value="">📚 Seleccione una carrera</option>
                            @foreach($carreras as $c)
                                <option value="{{ $c }}" {{ (isset($carrera) && $carrera == $c) ? 'selected' : '' }}>
                                    {{ $c }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Semestre</label>
                        <select name="semester" class="form-select" required>
                            <option value="">📅 Seleccione un semestre</option>
                            @foreach($semestres as $s)
                                <option value="{{ $s }}" {{ (isset($semestre) && $semestre == $s) ? 'selected' : '' }}>
                                    Semestre {{ $s }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Generar Reporte
                        </button>
                    </div>
                </div>
            </form>

            @if(isset($groupStats) && isset($carrera) && isset($semestre))
                {{-- BOTONES DE EXPORTACIÓN --}}
                <div class="mb-4 d-flex gap-2">
                    <a href="{{ route('admin.reports.group') }}?career={{ $carrera }}&semester={{ $semestre }}&format=pdf" 
                       class="btn btn-danger"
                       target="_blank">
                        <i class="fas fa-file-pdf"></i> Descargar PDF
                    </a>
                    
                    <a href="{{ route('admin.reports.group') }}?career={{ $carrera }}&semester={{ $semestre }}&format=csv" 
                       class="btn btn-success">
                        <i class="fas fa-file-csv"></i> Descargar CSV
                    </a>
                    
                    <button type="button" class="btn btn-info" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>

                {{-- INFORMACIÓN DEL REPORTE --}}
                <div class="alert alert-info mb-4">
                    <strong><i class="fas fa-info-circle"></i> Reporte generado para:</strong><br>
                    Carrera: <strong>{{ $carrera }}</strong> | 
                    Semestre: <strong>{{ $semestre }}</strong> |
                    Total de estudiantes: <strong>{{ $groupStats['total_estudiantes'] }}</strong>
                </div>

                {{-- ESTADÍSTICAS GENERALES --}}
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5>Total Estudiantes</h5>
                                <h2>{{ $groupStats['total_estudiantes'] }}</h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Promedio Grupo</h5>
                                <h2>{{ $groupStats['promedio_grupo'] }}%</h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5>Activos (30 días)</h5>
                                <h2>{{ $groupStats['activos_ultimo_mes'] }}</h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h5>En Riesgo</h5>
                                <h2>{{ $groupStats['en_riesgo'] }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RENDIMIENTO POR CATEGORÍA --}}
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">📊 Distribución de Rendimiento</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <span class="badge bg-success" style="font-size: 1.2rem;">
                                        {{ $groupStats['excelente'] }}
                                    </span>
                                </div>
                                <small class="text-muted">Excelente (≥90%)</small>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <span class="badge bg-primary" style="font-size: 1.2rem;">
                                        {{ $groupStats['aprobados'] }}
                                    </span>
                                </div>
                                <small class="text-muted">Aprobado (60-89%)</small>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <span class="badge bg-warning" style="font-size: 1.2rem;">
                                        {{ $groupStats['reprobados'] }}
                                    </span>
                                </div>
                                <small class="text-muted">Reprobado (50-59%)</small>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <span class="badge bg-danger" style="font-size: 1.2rem;">
                                        {{ $groupStats['en_riesgo'] }}
                                    </span>
                                </div>
                                <small class="text-muted">Riesgo (<50%)</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ÁREAS DÉBILES --}}
                @if(count($groupStats['areas_debiles']) > 0)
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">⚠️ Áreas Débiles del Grupo</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Materia</th>
                                        <th>Promedio</th>
                                        <th>Estudiantes Afectados</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupStats['areas_debiles'] as $area)
                                    <tr>
                                        <td><strong>{{ $area['materia'] }}</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $area['promedio'] < 50 ? 'danger' : 'warning' }}">
                                                {{ $area['promedio'] }}%
                                            </span>
                                        </td>
                                        <td>{{ $area['estudiantes_afectados'] }}</td>
                                        <td>
                                            @if($area['promedio'] < 50)
                                                <span class="text-danger">🔴 Crítico</span>
                                            @else
                                                <span class="text-warning">⚠️ Atención</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">✅ Áreas Débiles del Grupo</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success mb-0">
                            <strong>¡Excelente!</strong> No se detectaron áreas débiles. Todas las materias tienen un promedio superior al 60%.
                        </div>
                    </div>
                </div>
                @endif

                {{-- MEJORES ESTUDIANTES --}}
                @if(count($groupStats['mejores_estudiantes']) > 0)
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">🏆 Top 5 Mejores Estudiantes</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach($groupStats['mejores_estudiantes'] as $index => $student)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-warning text-dark me-2">#{{ $index + 1 }}</span>
                                    <strong>{{ $student['nombre'] }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $student['email'] }}</small>
                                </div>
                                <span class="badge bg-success" style="font-size: 1.1rem;">
                                    {{ round($student['score'], 2) }}%
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @else
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">🏆 Top 5 Mejores Estudiantes</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            No hay estudiantes con calificaciones registradas aún.
                        </div>
                    </div>
                </div>
                @endif

                {{-- ESTUDIANTES EN RIESGO --}}
                @if(count($groupStats['estudiantes_riesgo']) > 0)
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">🚨 Estudiantes en Riesgo</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <strong>⚠️ Atención:</strong> Estos estudiantes requieren intervención inmediata debido a su bajo rendimiento.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Puntuación</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupStats['estudiantes_riesgo'] as $student)
                                    <tr>
                                        <td>{{ $student['id'] }}</td>
                                        <td>{{ $student['nombre'] }}</td>
                                        <td>
                                            <span class="badge bg-danger">{{ $student['score'] }}%</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.students.show', $student['id']) }}" 
                                               class="btn btn-sm btn-primary">
                                                Ver Detalle
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">✅ Estudiantes en Riesgo</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success mb-0">
                            <strong>¡Excelente!</strong> No hay estudiantes en riesgo en este grupo.
                        </div>
                    </div>
                </div>
                @endif

            @else
                {{-- MENSAJE CUANDO NO HAY DATOS SELECCIONADOS --}}
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h4>Seleccione una carrera y semestre</h4>
                    <p>Para generar el informe de grupo, seleccione los filtros arriba y haga clic en "Generar Reporte".</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .card-header, .btn, form, .alert-info:first-of-type {
        display: none !important;
    }
}
</style>
@endpush
@endsection