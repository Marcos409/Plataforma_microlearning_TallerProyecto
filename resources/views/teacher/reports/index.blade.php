@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Reportes de Estudiantes</h4>
                    <a href="{{ route('teacher.reports.group') }}" class="btn btn-info">
                        Ver Reportes Grupales
                    </a>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card text-center bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">32</h5>
                                    <p class="card-text">Total de Estudiantes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-success">78%</h5>
                                    <p class="card-text">Promedio de Progreso</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-warning">5</h5>
                                    <p class="card-text">Estudiantes en Riesgo</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Generar Reporte Individual</h5>
                            <form class="mb-3">
                                <div class="form-group mb-2">
                                    <label>Seleccionar Estudiante:</label>
                                    <select class="form-control">
                                        <option>Juan Pérez - Ing. Sistemas</option>
                                        <option>María García - Ing. Industrial</option>
                                        <option>Carlos López - Ing. Civil</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label>Período:</label>
                                    <select class="form-control">
                                        <option>Última semana</option>
                                        <option>Último mes</option>
                                        <option>Último semestre</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary">Generar Reporte</button>
                            </form>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Reporte por Materia</h5>
                            <form>
                                <div class="form-group mb-2">
                                    <label>Seleccionar Materia:</label>
                                    <select class="form-control">
                                        <option>Matemáticas</option>
                                        <option>Física</option>
                                        <option>Química</option>
                                        <option>Programación</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label>Tipo de Reporte:</label>
                                    <select class="form-control">
                                        <option>Rendimiento General</option>
                                        <option>Diagnósticos</option>
                                        <option>Actividades Completadas</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-success">Generar Reporte</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection