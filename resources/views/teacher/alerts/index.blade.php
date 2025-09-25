@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Alertas de Estudiantes</h4>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Sistema de Alertas</strong><br>
                        Aquí aparecerán las alertas de estudiantes que requieren atención.
                    </div>

                    <!-- Ejemplo de alerta -->
                    <div class="card mb-3 border-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title text-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Estudiante con bajo rendimiento
                                    </h6>
                                    <p class="card-text">
                                        El estudiante Juan Pérez no ha completado actividades en los últimos 7 días.
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i>
                                        Hace 2 horas
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-outline-success">
                                    Marcar como Resuelto
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Otro ejemplo -->
                    <div class="card mb-3 border-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title text-danger">
                                        <i class="fas fa-times-circle"></i>
                                        Múltiples diagnósticos fallidos
                                    </h6>
                                    <p class="card-text">
                                        María García ha fallado 3 diagnósticos consecutivos en Matemáticas.
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i>
                                        Hace 1 día
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-outline-success">
                                    Marcar como Resuelto
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection