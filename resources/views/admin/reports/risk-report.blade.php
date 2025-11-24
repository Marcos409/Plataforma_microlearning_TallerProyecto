{{-- resources/views/admin/reports/risk-report.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0">
                <i class="fas fa-exclamation-triangle"></i> Informe Semanal de Estudiantes en Riesgo
            </h3>
            <div>
                <span class="badge bg-light text-dark">
                    Semana {{ $reportStats['report_week'] }}/{{ $reportStats['report_year'] }}
                </span>
            </div>
        </div>
        
        <div class="card-body">
            {{-- BOTONES DE EXPORTACIÓN --}}
            <div class="mb-4 d-flex gap-2 justify-content-between align-items-center">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reports.risk-report') }}?format=pdf&date={{ $date }}" 
                       class="btn btn-danger"
                       target="_blank">
                        <i class="fas fa-file-pdf"></i> Descargar PDF
                    </a>
                    
                    <a href="{{ route('admin.reports.risk-report') }}?format=csv&date={{ $date }}" 
                       class="btn btn-success">
                        <i class="fas fa-file-csv"></i> Descargar Excel
                    </a>
                    
                    <button type="button" class="btn btn-info" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    
                </div>
                
                <div>
                    <small class="text-muted">
                        <i class="fas fa-calendar"></i> Generado: {{ $reportStats['generation_date'] }}
                    </small>
                </div>
            </div>

            {{-- CRITERIOS DE RIESGO APLICADOS --}}
            <div class="alert alert-info mb-4">
                <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Criterios de Riesgo Aplicados:</h6>
                <ul class="mb-0">
                    <li>Puntaje menor al <strong>{{ $riskCriteria['low_score_threshold'] }}%</strong></li>
                    <li>Falta de actividad durante más de <strong>{{ $riskCriteria['inactivity_days'] }} días</strong></li>
                    <li>Menos de <strong>{{ $riskCriteria['min_activities'] }} actividades</strong> completadas</li>
                </ul>
            </div>

            {{-- RESUMEN EJECUTIVO --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Estudiantes</h6>
                            <h2 class="mb-0">{{ $reportStats['total_students'] }}</h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <h6 class="text-muted">En Riesgo</h6>
                            <h2 class="mb-0 text-danger">{{ $reportStats['total_risk_students'] }}</h2>
                            <small class="text-muted">{{ $reportStats['percentage_at_risk'] }}%</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h6> Crítico</h6>
                            <h3 class="mb-0">{{ $reportStats['critical'] }}</h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h6> Alto</h6>
                            <h3 class="mb-0">{{ $reportStats['high'] }}</h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h6> Moderado</h6>
                            <h3 class="mb-0">{{ $reportStats['moderate'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RECOMENDACIONES AUTOMÁTICAS --}}
            @if(count($recommendations) > 0)
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb"></i> Recomendaciones de Acción
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($recommendations as $rec)
                    <div class="alert alert-{{ $rec['priority'] == 'urgent' ? 'danger' : ($rec['priority'] == 'high' ? 'warning' : 'info') }} mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="alert-heading mb-1">
                                    @if($rec['priority'] == 'urgent')
                                        🚨 URGENTE
                                    @elseif($rec['priority'] == 'high')
                                        ⚠️ ALTA PRIORIDAD
                                    @else
                                        📌 PRIORIDAD MEDIA
                                    @endif
                                    - {{ $rec['title'] }}
                                </h6>
                                <p class="mb-1">{{ $rec['description'] }}</p>
                                <small class="text-muted">
                                    Estudiantes afectados: {{ count($rec['students']) }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- TABLA DE ESTUDIANTES EN RIESGO --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        📋 Detalle de Estudiantes en Riesgo ({{ $reportStats['total_risk_students'] }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($riskStudents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm" id="riskTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Nivel</th>
                                        <th>Estudiante</th>
                                        <th>Carrera/Sem</th>
                                        <th>Promedio</th>
                                        <th>Actividades</th>
                                        <th>Último Acceso</th>
                                        <th>Factores de Riesgo</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($riskStudents as $student)
                                    <tr class="align-middle">
                                        <td>
                                            @if($student['risk_category'] == 'critico')
                                                <span class="badge bg-danger">🔴 CRÍTICO</span>
                                            @elseif($student['risk_category'] == 'alto')
                                                <span class="badge bg-warning text-dark">🟠 ALTO</span>
                                            @else
                                                <span class="badge bg-secondary">🟡 MODERADO</span>
                                            @endif
                                        </td>
                                        
                                        <td>
                                            <strong>{{ $student['nombre'] }}</strong><br>
                                            <small class="text-muted">{{ $student['email'] }}</small>
                                        </td>
                                        
                                        <td>
                                            <small>
                                                {{ Str::limit($student['carrera'], 15) }}<br>
                                                Sem: {{ $student['semestre'] }}
                                            </small>
                                        </td>
                                        
                                        <td>
                                            <span class="badge bg-{{ $student['avg_score'] < 40 ? 'danger' : 'warning' }}">
                                                {{ $student['avg_score'] }}%
                                            </span>
                                        </td>
                                        
                                        <td class="text-center">
                                            {{ $student['total_activities'] }}
                                        </td>
                                        
                                        <td>
                                            @if($student['last_activity'])
                                                <small>
                                                    {{ \Carbon\Carbon::parse($student['last_activity'])->format('d/m/Y') }}<br>
                                                    <span class="text-danger">
                                                        ({{ $student['days_inactive'] }} días)
                                                    </span>
                                                </small>
                                            @else
                                                <span class="badge bg-dark">Sin actividad</span>
                                            @endif
                                        </td>
                                        
                                        <td>
                                            <ul class="list-unstyled mb-0" style="font-size: 0.85rem;">
                                                @foreach($student['risk_factors'] as $factor)
                                                <li>
                                                    <span class="badge bg-{{ $factor['severity'] == 'critical' ? 'danger' : ($factor['severity'] == 'high' ? 'warning' : 'secondary') }} mb-1">
                                                        {{ $factor['factor'] }}
                                                    </span><br>
                                                    <small class="text-muted">{{ $factor['detail'] }}</small>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm">
                                                <a href="{{ route('admin.students.show', $student['id']) }}" 
                                                   class="btn btn-primary btn-sm"
                                                   title="Ver perfil">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-success btn-sm"
                                                        onclick="sendEmail('{{ $student['email'] }}')"
                                                        title="Enviar email">
                                                    <i class="fas fa-envelope"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h4>¡Excelente!</h4>
                            <p>No se detectaron estudiantes en riesgo esta semana.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PARA ENVÍO DE EMAIL --}}
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-envelope"></i> Enviar Informe por Email
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .btn, .card-header, .alert-info:first-of-type, .modal {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}

#riskTable tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
@endpush

@push('scripts')
<script>
function sendEmail(email) {
    window.location.href = `mailto:${email}?subject=Seguimiento Estudiante en Riesgo&body=Estimado/a estudiante,%0D%0A%0D%0AHemos detectado...`;
}

// DataTable para ordenar/filtrar
$(document).ready(function() {
    if ($('#riskTable tbody tr').length > 0) {
        $('#riskTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[0, 'desc']], // Ordenar por nivel de riesgo
            pageLength: 25
        });
    }
});
</script>
@endpush
@endsection