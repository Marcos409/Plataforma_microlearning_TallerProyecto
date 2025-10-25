@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-exclamation-triangle me-2"></i>Alertas de Riesgo</h1>
                    <p class="text-muted mb-0">Estudiantes que requieren atención especial</p>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver a Reportes
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Resumen de Alertas -->
            <div class="row mb-4">
                @php
                    $criticalCount = $atRiskStudents->where('risk_level', 3)->count();
                    $highCount = $atRiskStudents->where('risk_level', 2)->count();
                    $mediumCount = $atRiskStudents->where('risk_level', 1)->count();
                    $totalAtRisk = $atRiskStudents->count();
                @endphp

                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-dark text-white h-100">
                        <div class="card-body">
                            <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                            <h3 class="card-title mb-1">{{ $criticalCount }}</h3>
                            <p class="card-text mb-0">Riesgo Crítico</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-danger text-white h-100">
                        <div class="card-body">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <h3 class="card-title mb-1">{{ $highCount }}</h3>
                            <p class="card-text mb-0">Riesgo Alto</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-warning text-white h-100">
                        <div class="card-body">
                            <i class="fas fa-exclamation fa-2x mb-2"></i>
                            <h3 class="card-title mb-1">{{ $mediumCount }}</h3>
                            <p class="card-text mb-0">Riesgo Medio</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-primary text-white h-100">
                        <div class="card-body">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h3 class="card-title mb-1">{{ $totalAtRisk }}</h3>
                            <p class="card-text mb-0">Total en Riesgo</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Estudiantes en Riesgo -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Estudiantes que Requieren Atención</h5>
                </div>
                <div class="card-body">
                    @forelse($atRiskStudents as $student)
                    <div class="alert alert-{{ $student->risk_level == 3 ? 'dark' : ($student->risk_level == 2 ? 'danger' : 'warning') }} mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-white text-dark me-3">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong class="d-block">{{ $student->name }}</strong>
                                        <small>{{ $student->email }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2 text-center">
                                @php
                                    $riskLabels = [
                                        3 => ['text' => 'CRÍTICO', 'icon' => 'exclamation-circle'],
                                        2 => ['text' => 'ALTO', 'icon' => 'exclamation-triangle'],
                                        1 => ['text' => 'MEDIO', 'icon' => 'exclamation']
                                    ];
                                    $risk = $riskLabels[$student->risk_level];
                                @endphp
                                <i class="fas fa-{{ $risk['icon'] }} fa-2x mb-1"></i>
                                <br>
                                <strong>{{ $risk['text'] }}</strong>
                            </div>

                            <div class="col-md-4">
                                <strong class="d-block mb-2">Factores de Riesgo:</strong>
                                <ul class="mb-0 small">
                                    @foreach($student->risk_factors as $factor)
                                        <li>{{ $factor }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="col-md-2 text-center">
                                <small class="text-muted d-block">Último acceso</small>
                                @if($student->last_activity)
                                    <strong>{{ $student->last_activity->diffForHumans() }}</strong>
                                @else
                                    <strong class="text-danger">Nunca</strong>
                                @endif
                            </div>

                            <div class="col-md-1 text-center">
                                <a href="{{ route('admin.students.show', $student->id) }}" 
                                   class="btn btn-sm btn-outline-dark"
                                   title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Estadísticas del Estudiante -->
                        <div class="row mt-3 pt-3 border-top">
                            <div class="col-md-4">
                                <small class="text-muted">Promedio General:</small>
                                <div class="progress mt-1" style="height: 20px;">
                                    @php
                                        $totalResponses = $student->diagnosticResponses->count();
                                        $correctResponses = $student->diagnosticResponses->where('is_correct', true)->count();
                                        $avg = $totalResponses > 0 ? round(($correctResponses / $totalResponses) * 100) : 0;
                                        $color = $avg >= 70 ? 'success' : ($avg >= 50 ? 'warning' : 'danger');
                                    @endphp
                                    <div class="progress-bar bg-{{ $color }}" style="width: {{ $avg }}%">
                                        {{ $avg }}%
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <small class="text-muted d-block">Diagnósticos Completados</small>
                                <strong class="fs-5">{{ $student->diagnosticResponses->pluck('diagnostic_id')->unique()->count() }}</strong>
                            </div>
                            <div class="col-md-4 text-center">
                                <small class="text-muted d-block">Actividades Completadas</small>
                                <strong class="fs-5">{{ $student->getCompletedActivitiesCount() }}</strong>
                            </div>
                        </div>

                        <!-- Acciones Recomendadas -->
                        <div class="mt-3 pt-3 border-top">
                            <strong class="d-block mb-2">Acciones Recomendadas:</strong>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" 
                                        class="btn btn-outline-primary"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#emailModal{{ $student->id }}">
                                    <i class="fas fa-envelope me-1"></i>Enviar Email
                                </button>
                                <a href="tel:{{ $student->phone ?? '' }}" 
                                   class="btn btn-outline-success {{ !$student->phone ? 'disabled' : '' }}"
                                   {{ !$student->phone ? 'title=Sin teléfono registrado' : '' }}>
                                    <i class="fas fa-phone me-1"></i>Contactar
                                </a>
                                <button type="button" 
                                        class="btn btn-outline-info"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#followUpModal{{ $student->id }}">
                                    <i class="fas fa-calendar me-1"></i>Agendar Seguimiento
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal para Enviar Email -->
                    <div class="modal fade" id="emailModal{{ $student->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-envelope me-2"></i>Enviar Email a {{ $student->name }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.reports.send-email', $student->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Para:</label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   value="{{ $student->email }}" 
                                                   readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Asunto:</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="subject" 
                                                   value="Seguimiento Académico - Plataforma UC"
                                                   required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Plantilla:</label>
                                            <select class="form-select" onchange="fillEmailTemplate({{ $student->id }}, this.value)">
                                                <option value="">Seleccionar plantilla...</option>
                                                <option value="inactivity">Inactividad en la plataforma</option>
                                                <option value="low_performance">Bajo rendimiento</option>
                                                <option value="motivation">Motivación y apoyo</option>
                                                <option value="custom">Mensaje personalizado</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Mensaje:</label>
                                            <textarea class="form-control" 
                                                      name="message" 
                                                      id="emailMessage{{ $student->id }}"
                                                      rows="6" 
                                                      required 
                                                      placeholder="Escribe tu mensaje aquí..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-1"></i>Enviar Email
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal para Agendar Seguimiento -->
                    <div class="modal fade" id="followUpModal{{ $student->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-calendar me-2"></i>Agendar Seguimiento - {{ $student->name }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.reports.schedule-followup', $student->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Tipo de Seguimiento:</label>
                                            <select class="form-select" name="type" required>
                                                <option value="">Seleccionar...</option>
                                                <option value="meeting">Reunión presencial</option>
                                                <option value="call">Llamada telefónica</option>
                                                <option value="video_call">Videollamada</option>
                                                <option value="email">Seguimiento por email</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Fecha:</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   name="date" 
                                                   min="{{ date('Y-m-d') }}"
                                                   required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Hora:</label>
                                            <input type="time" 
                                                   class="form-control" 
                                                   name="time" 
                                                   required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Notas:</label>
                                            <textarea class="form-control" 
                                                      name="notes" 
                                                      rows="4" 
                                                      placeholder="Motivo del seguimiento, temas a tratar, etc."></textarea>
                                        </div>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <small>Se enviará un recordatorio al estudiante 24 horas antes de la cita.</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-info">
                                            <i class="fas fa-calendar-check me-1"></i>Agendar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    @empty
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4 class="text-success">¡Excelente!</h4>
                        <p class="text-muted">No hay estudiantes en situación de riesgo en este momento</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.5rem;
}

.card {
    border: none;
}

.alert {
    border: none;
}
</style>

<script>
// Plantillas de email predefinidas
const emailTemplates = {
    inactivity: `Estimado/a estudiante,

Hemos notado que no has ingresado a la plataforma en los últimos días. Tu progreso académico es importante para nosotros.

Te invitamos a retomar tus actividades y aprovechar los recursos disponibles. Si tienes alguna dificultad, estamos aquí para apoyarte.

Saludos cordiales,
Equipo Académico UC`,
    
    low_performance: `Estimado/a estudiante,

Hemos revisado tu rendimiento académico y queremos ofrecerte nuestro apoyo para mejorar tus resultados.

Te sugerimos:
- Revisar los materiales de estudio adicionales
- Participar en las sesiones de tutoría disponibles
- Contactarnos si necesitas orientación personalizada

Estamos comprometidos con tu éxito académico.

Saludos cordiales,
Equipo Académico UC`,
    
    motivation: `Estimado/a estudiante,

Queremos recordarte que tu esfuerzo y dedicación son fundamentales para alcanzar tus metas académicas.

Reconocemos los desafíos que enfrentas, pero confiamos en tu capacidad para superarlos. Estamos aquí para apoyarte en cada paso de tu camino educativo.

¡Sigue adelante! Cuentas con todo nuestro apoyo.

Saludos cordiales,
Equipo Académico UC`,
    
    custom: ''
};

function fillEmailTemplate(studentId, template) {
    const messageField = document.getElementById('emailMessage' + studentId);
    if (template && emailTemplates[template] !== undefined) {
        messageField.value = emailTemplates[template];
    }
}
</script>
@endsection