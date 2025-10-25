@extends('layouts.app')

@section('title', 'Panel de Administrador')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-shield me-2"></i>Panel de Administrador
                    </h5>
                </div>
                
                <div class="card-body bg-primary text-white">
                    <div class="text-center mb-4">
                        <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user text-primary fa-2x"></i>
                        </div>
                        <p class="text-white mt-2 mb-1 fw-bold">{{ Auth::user()->name }}</p>
                        
                        @php
                            $userRoleId = Auth::user()->role_id;
                            $userRole = $userRoleId ? \App\Models\Role::find($userRoleId) : null;
                        @endphp
                        
                        @if($userRole)
                            <span class="badge bg-danger">
                                <i class="fas fa-crown me-1"></i>{{ $userRole->name }}
                            </span>
                        @else
                            <small class="text-light">Usuario</small>
                        @endif
                    </div>
                    
                    <div class="border-top pt-3">
                        <p class="mb-2 small">
                            <i class="fas fa-envelope me-2"></i>{{ Auth::user()->email }}
                        </p>
                        @if(Auth::user()->phone)
                        <p class="mb-2 small">
                            <i class="fas fa-phone me-2"></i>{{ Auth::user()->phone }}
                        </p>
                        @endif
                    </div>
                </div>
                
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.settings.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog me-2"></i>Configuración
                    </a>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="col-md-9">
            <!-- Sección Dashboard -->
            <div id="section-dashboard" class="content-section">
                <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard General</h2>
                
                @php
                    $totalUsers = App\Models\User::count();
                    $adminCount = App\Models\User::whereHas('role', function($q) { $q->where('name', 'Administrador'); })->count();
                    $docenteCount = App\Models\User::whereHas('role', function($q) { $q->where('name', 'Docente'); })->count();
                    $estudianteCount = App\Models\User::whereHas('role', function($q) { $q->where('name', 'Estudiante'); })->count();
                    
                    
                    // Usuarios nuevos en los últimos 7 días
                    $newUsersWeek = App\Models\User::where('created_at', '>=', now()->subDays(7))->count();
                    
                    // Crecimiento del mes actual vs mes anterior
                    $currentMonthUsers = App\Models\User::whereMonth('created_at', now()->month)
                                                        ->whereYear('created_at', now()->year)
                                                        ->count();
                    $lastMonthUsers = App\Models\User::whereMonth('created_at', now()->subMonth()->month)
                                                      ->whereYear('created_at', now()->subMonth()->year)
                                                      ->count();
                    
                    $growthPercentage = $lastMonthUsers > 0 ? round((($currentMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 1) : 0;
                    
                    // Calcular porcentajes
                    $adminPercent = $totalUsers > 0 ? round(($adminCount / $totalUsers) * 100, 1) : 0;
                    $docentePercent = $totalUsers > 0 ? round(($docenteCount / $totalUsers) * 100, 1) : 0;
                    $estudiantePercent = $totalUsers > 0 ? round(($estudianteCount / $totalUsers) * 100, 1) : 0;
                    $sinRolPercent = $totalUsers > 0 ? round(($pendingUsersCount / $totalUsers) * 100, 1) : 0;
                @endphp

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h4>{{ $totalUsers }}</h4>
                                <p class="mb-0">Total Usuarios</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h4>{{ $pendingUsersCount }}</h4>
                                <p class="mb-0">Sin Rol Asignado</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-user-graduate fa-2x mb-2"></i>
                                <h4>{{ $estudianteCount }}</h4>
                                <p class="mb-0">Estudiantes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                                <h4>{{ $docenteCount }}</h4>
                                <p class="mb-0">Profesores</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accesos rápidos -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="d-grid">
                                            <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                                                <i class="fas fa-users me-2"></i>Ver Todos los Usuarios
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-grid">
                                            <a href="{{ route('admin.users.pending') }}?filter=pending" class="btn btn-warning">
                                                <i class="fas fa-clock me-2"></i>Usuarios Pendientes
                                                @if($pendingUsersCount > 0)
                                                    <span class="badge bg-dark ms-1">{{ $pendingUsersCount }}</span>
                                                @endif
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-grid">
                                            <a href="{{ route('admin.roles.index') }}" class="btn btn-success">
                                                <i class="fas fa-user-tag me-2"></i>Gestionar Roles
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-grid">
                                            <a href="{{ route('admin.users.create') }}" class="btn btn-info">
                                                <i class="fas fa-plus me-2"></i>Crear Nuevo Usuario
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Distribución de Roles -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Distribución de Roles</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Gráfico de Dona -->
                                    <div class="col-md-5">
                                        <div class="d-flex align-items-center justify-content-center" style="height: 100%;">
                                            <div style="width: 100%; max-width: 350px;">
                                                <canvas id="roleChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Resumen Detallado -->
                                    <div class="col-md-7">
                                        <h6 class="fw-bold mb-3">Resumen Detallado:</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <span class="badge" style="background-color: #dc3545; width: 15px; height: 15px; display: inline-block;"></span>
                                                <strong>Administradores:</strong> {{ $adminCount }} <small class="text-muted">({{ $adminPercent }}%)</small>
                                            </li>
                                            <li class="mb-2">
                                                <span class="badge" style="background-color: #28a745; width: 15px; height: 15px; display: inline-block;"></span>
                                                <strong>Docentes:</strong> {{ $docenteCount }} <small class="text-muted">({{ $docentePercent }}%)</small>
                                            </li>
                                            <li class="mb-2">
                                                <span class="badge" style="background-color: #007bff; width: 15px; height: 15px; display: inline-block;"></span>
                                                <strong>Estudiantes:</strong> {{ $estudianteCount }} <small class="text-muted">({{ $estudiantePercent }}%)</small>
                                            </li>
                                            <li class="mb-3">
                                                <span class="badge" style="background-color: #ffc107; width: 15px; height: 15px; display: inline-block;"></span>
                                                <strong>Pendientes:</strong> {{ $pendingUsersCount }} <small class="text-muted">({{ $sinRolPercent }}%)</small>
                                                @if($pendingUsersCount > 0)
                                                    <i class="fas fa-exclamation-triangle text-warning ms-1"></i>
                                                @endif
                                            </li>
                                        </ul>
                                        
                                        @if($pendingUsersCount > 0)
                                            <div class="alert alert-warning py-2 px-3 mb-3" role="alert">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                <small><strong>{{ $pendingUsersCount }}</strong> usuario(s) requiere(n) asignación de rol</small>
                                            </div>
                                        @endif
                                        
                                        <hr class="my-3">
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="small">
                                                    <p class="mb-2">
                                                        <i class="fas fa-user-plus me-1 text-primary"></i>
                                                        <strong>Nuevos esta semana:</strong><br>
                                                        <span class="fs-5">{{ $newUsersWeek }}</span> usuario(s)
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small">
                                                    <p class="mb-0">
                                                        <i class="fas fa-chart-line me-1 text-primary"></i>
                                                        <strong>Crecimiento mes actual:</strong><br>
                                                        @if($growthPercentage > 0)
                                                            <span class="fs-5 text-success">+{{ $growthPercentage }}%</span>
                                                        @elseif($growthPercentage < 0)
                                                            <span class="fs-5 text-danger">{{ $growthPercentage }}%</span>
                                                        @else
                                                            <span class="fs-5 text-muted">0%</span>
                                                        @endif
                                                        <small class="text-muted">vs mes anterior</small>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Esperar un momento para asegurar que todo esté cargado
        setTimeout(function() {
            const chartCanvas = document.getElementById('roleChart');
            
            if (chartCanvas) {
                console.log('Canvas encontrado, inicializando gráfico...');
                
                const ctx = chartCanvas.getContext('2d');
                
                const data = {
                    labels: ['Administradores', 'Docentes', 'Estudiantes', 'Sin Asignar'],
                    datasets: [{
                        label: 'Usuarios',
                        data: [
                            {{ $adminCount }},
                            {{ $docenteCount }},
                            {{ $estudianteCount }},
                            {{ $pendingUsersCount}}
                        ],
                        backgroundColor: [
                            '#dc3545',
                            '#28a745',
                            '#007bff',
                            '#ffc107'
                        ],
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                };
                
                const config = {
                    type: 'doughnut',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 13,
                                        family: "'Segoe UI', Arial, sans-serif"
                                    },
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                enabled: true,
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    size: 14
                                },
                                bodyFont: {
                                    size: 13
                                },
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return label + ': ' + value + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                };
                
                try {
                    const myChart = new Chart(ctx, config);
                    console.log('Gráfico creado exitosamente');
                } catch (error) {
                    console.error('Error al crear el gráfico:', error);
                }
            } else {
                console.error('No se encontró el canvas con id "roleChart"');
            }
        }, 100);
    });
</script>
@endpush