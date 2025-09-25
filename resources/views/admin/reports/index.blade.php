@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Centro de Reportes - Administrador</h4>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">245</h5>
                                    <p class="card-text">Total Estudiantes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">18</h5>
                                    <p class="card-text">Docentes Activos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">1,247</h5>
                                    <p class="card-text">Actividades Completadas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">89%</h5>
                                    <p class="card-text">Satisfacción Promedio</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-users"></i>
                                        Reportes de Estudiantes
                                    </h5>
                                    <p class="card-text">Progreso, rendimiento y estadísticas detalladas de estudiantes.</p>
                                    <a href="{{ route('admin.reports.students') }}" class="btn btn-primary">Ver Reportes</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-line"></i>
                                        Reportes de Rendimiento
                                    </h5>
                                    <p class="card-text">Análisis de rendimiento general y por materias.</p>
                                    <a href="{{ route('admin.reports.performance') }}" class="btn btn-success">Ver Reportes</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Alertas de Riesgo
                                    </h5>
                                    <p class="card-text">Estudiantes que requieren atención especial.</p>
                                    <a href="{{ route('admin.reports.risk') }}" class="btn btn-warning">Ver Alertas</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Generar Reporte Personalizado</h5>
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Tipo de Reporte:</label>
                                                    <select class="form-control">
                                                        <option>Progreso General</option>
                                                        <option>Diagnósticos</option>
                                                        <option>Uso de Plataforma</option>
                                                        <option>Rendimiento por Carrera</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Período:</label>
                                                    <select class="form-control">
                                                        <option>Última semana</option>
                                                        <option>Último mes</option>
                                                        <option>Último trimestre</option>
                                                        <option>Último año</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Formato:</label>
                                                    <select class="form-control">
                                                        <option>PDF</option>
                                                        <option>Excel</option>
                                                        <option>CSV</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-primary form-control">
                                                        Generar Reporte
                                                    </button>
                                                </div>
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
    </div>
</div>
@endsection