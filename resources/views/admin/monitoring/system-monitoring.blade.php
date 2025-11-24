@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-chart-area"></i> Monitoreo del Sistema
        </h1>
        <div>
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Tarjetas de Métricas Principales --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Usuarios Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['usuarios_activos'] ?? 0 }}
                            </div>
                            <small class="text-muted">Últimas 24 horas</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Sesiones Hoy
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['sesiones_hoy'] ?? 0 }}
                            </div>
                            <small class="text-muted">Total del día</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sign-in-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Actividades Completadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['actividades_completadas'] ?? 0 }}
                            </div>
                            <small class="text-muted">Esta semana</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tiempo Promedio Sesión
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['tiempo_promedio_sesion'] ?? 0, 0) }} min
                            </div>
                            <small class="text-muted">Por usuario</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Gráfico de Actividad por Hora --}}
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Actividad por Hora (Hoy)</h6>
                </div>
                <div class="card-body">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Distribución de Usuarios --}}
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución de Usuarios</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px; max-height: 300px;">
                        <canvas id="usersChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> Estudiantes
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Profesores
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> Admins
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Usuarios Activos Recientes --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Usuarios Activos Recientemente</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Última Actividad</th>
                                    <th>Sesiones Hoy</th>
                                    <th>Tiempo Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usuarios_activos as $usuario)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                {{ substr($usuario->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <strong>{{ $usuario->name }}</strong><br>
                                                <small class="text-muted">{{ $usuario->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $usuario->role_name == 'Admin' ? 'danger' : ($usuario->role_name == 'Teacher' ? 'success' : 'primary') }}">
                                            {{ $usuario->role_name ?? 'Student' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($usuario->last_activity)
                                            {{ \Carbon\Carbon::parse($usuario->last_activity)->diffForHumans() }}
                                        @else
                                            Nunca
                                        @endif
                                    </td>
                                    <td>{{ $usuario->sesiones_hoy ?? 0 }}</td>
                                    <td>{{ number_format($usuario->tiempo_total ?? 0, 0) }} min</td>
                                    <td>
                                        @php
                                            $minutosDesdeUltimaActividad = $usuario->last_activity 
                                                ? now()->diffInMinutes(\Carbon\Carbon::parse($usuario->last_activity)) 
                                                : 999;
                                        @endphp
                                        @if($minutosDesdeUltimaActividad < 15)
                                            <span class="badge bg-success">
                                                <i class="fas fa-circle"></i> En línea
                                            </span>
                                        @elseif($minutosDesdeUltimaActividad < 60)
                                            <span class="badge bg-warning">
                                                <i class="fas fa-circle"></i> Ausente
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-circle"></i> Desconectado
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        No hay usuarios activos recientemente
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estadísticas de Uso del Sistema --}}
    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contenido Más Accedido</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($contenido_mas_accedido ?? [] as $contenido)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $contenido->title ?? 'Sin título' }}</strong><br>
                                <small class="text-muted">{{ $contenido->content_type ?? 'N/A' }}</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">
                                {{ $contenido->vistas ?? 0 }} vistas
                            </span>
                        </li>
                        @empty
                        <li class="list-group-item text-center text-muted">
                            No hay datos disponibles
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actividad por Día (Última Semana)</h6>
                </div>
                <div class="card-body">
                    <canvas id="weeklyActivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de Actividad por Hora
const activityCtx = document.getElementById('activityChart').getContext('2d');
new Chart(activityCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($actividad_por_hora['labels'] ?? ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00']) !!},
        datasets: [{
            label: 'Usuarios Activos',
            data: {!! json_encode($actividad_por_hora['data'] ?? [0, 0, 5, 15, 20, 10]) !!},
            borderColor: 'rgb(78, 115, 223)',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Gráfico de Distribución de Usuarios
const usersCtx = document.getElementById('usersChart').getContext('2d');
new Chart(usersCtx, {
    type: 'doughnut',
    data: {
        labels: ['Estudiantes', 'Profesores', 'Admins'],
        datasets: [{
            data: {!! json_encode($distribucion_usuarios ?? [50, 5, 2]) !!},
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc']
        }]
    },
    options: {
        responsive: true,
        // **Este ajuste ya está correcto, lo que desactiva el aspecto de estiramiento**
        maintainAspectRatio: false 
    }
});

// Gráfico de Actividad Semanal
const weeklyCtx = document.getElementById('weeklyActivityChart').getContext('2d');
new Chart(weeklyCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($actividad_semanal['labels'] ?? ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']) !!},
        datasets: [{
            label: 'Actividades',
            data: {!! json_encode($actividad_semanal['data'] ?? [12, 19, 15, 25, 22, 8, 5]) !!},
            backgroundColor: 'rgba(78, 115, 223, 0.8)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Auto-refresh cada 30 segundos
setTimeout(() => location.reload(), 30000);
</script>
@endsection