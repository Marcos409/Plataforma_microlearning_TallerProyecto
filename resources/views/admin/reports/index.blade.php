@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Centro de Reportes</h4>
                    
                    
                    
                </div>
                

                <div class="card-body">
                    <!-- Estadísticas Generales -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card text-center bg-primary text-white h-100">
                                <div class="card-body">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <h3 class="card-title mb-1">{{ $totalStudents ?? 0 }}</h3>
                                    <p class="card-text mb-0">Total Estudiantes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card text-center bg-success text-white h-100">
                                <div class="card-body">
                                    <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                                    <h3 class="card-title mb-1">{{ $activeTeachers ?? 0 }}</h3>
                                    <p class="card-text mb-0">Docentes Activos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card text-center bg-warning text-white h-100">
                                <div class="card-body">
                                    <i class="fas fa-tasks fa-2x mb-2"></i>
                                    <h3 class="card-title mb-1">{{ $completedActivities ?? 0 }}</h3>
                                    <p class="card-text mb-0">Actividades Completadas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card text-center bg-info text-white h-100">
                                <div class="card-body">
                                    <i class="fas fa-smile fa-2x mb-2"></i>
                                    <h3 class="card-title mb-1">{{ $averageSatisfaction ?? 0 }}%</h3>
                                    <p class="card-text mb-0">Satisfacción Promedio</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tipos de Reportes -->
                    <h5 class="mb-3"><i class="fas fa-file-alt me-2"></i>Tipos de Reportes</h5>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm hover-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-box bg-primary text-white rounded-circle p-3 me-3">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-1">Reportes de Estudiantes</h5>
                                            <small class="text-muted">Progreso y estadísticas</small>
                                        </div>
                                    </div>
                                    <p class="card-text">Información detallada sobre el progreso, rendimiento y participación de cada estudiante.</p>
                                    <a href="{{ route('admin.reports.students') }}" class="btn btn-primary w-100">
                                        <i class="fas fa-chart-line me-1"></i>Ver Reportes
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm hover-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-box bg-success text-white rounded-circle p-3 me-3">
                                            <i class="fas fa-chart-line fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-1">Reportes de Rendimiento</h5>
                                            <small class="text-muted">Análisis general</small>
                                        </div>
                                    </div>
                                    <p class="card-text">Análisis de rendimiento académico general, por materias y comparativas entre grupos.</p>
                                    <a href="{{ route('admin.reports.performance') }}" class="btn btn-success w-100">
                                        <i class="fas fa-chart-bar me-1"></i>Ver Reportes
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm hover-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-box bg-warning text-white rounded-circle p-3 me-3">
                                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-1">Alertas de Riesgo</h5>
                                            <small class="text-muted">Atención especial</small>
                                        </div>
                                    </div>
                                    <p class="card-text">Estudiantes que requieren atención inmediata por bajo rendimiento o inactividad.</p>
                                    <a href="{{ route('admin.reports.risk') }}" class="btn btn-warning w-100">
                                        <i class="fas fa-bell me-1"></i>Ver Alertas
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Generar Reporte Personalizado -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-file-download me-2"></i>Generar Reporte Personalizado</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.reports.generate') }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Tipo de Reporte:</label>
                                        <select class="form-select" name="report_type" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="general">Progreso General</option>
                                            <option value="diagnostics">Diagnósticos</option>
                                            <option value="usage">Uso de Plataforma</option>
                                            <option value="career">Rendimiento por Carrera</option>
                                            <option value="subject">Rendimiento por Materia</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Período:</label>
                                        <select class="form-select" name="period" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="week">Última semana</option>
                                            <option value="month">Último mes</option>
                                            <option value="quarter">Último trimestre</option>
                                            <option value="year">Último año</option>
                                            <option value="all">Todo el tiempo</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Formato:</label>
                                        <select class="form-select" name="format" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="pdf">PDF</option>
                                            <option value="excel">Excel</option>
                                            <option value="csv">CSV</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-download me-1"></i>Generar Reporte
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
}

.icon-box {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card {
    border: none;
}
</style>
@endsection