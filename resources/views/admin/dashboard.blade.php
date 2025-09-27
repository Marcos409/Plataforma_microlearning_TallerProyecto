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
                <div class="list-group list-group-flush">
                    <a href="#dashboard" class="list-group-item list-group-item-action active" data-section="dashboard">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="#users" class="list-group-item list-group-item-action" data-section="users">
                        <i class="fas fa-users me-2"></i>Gestión de Usuarios
                    </a>
                    <a href="#roles" class="list-group-item list-group-item-action" data-section="roles">
                        <i class="fas fa-user-tag me-2"></i>Asignación de Roles
                    </a>
                    <a href="#reports" class="list-group-item list-group-item-action" data-section="reports">
                        <i class="fas fa-chart-bar me-2"></i>Reportes
                    </a>
                    <a href="#settings" class="list-group-item list-group-item-action" data-section="settings">
                        <i class="fas fa-cogs me-2"></i>Configuración
                    </a>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="col-md-9">
            <!-- Sección Dashboard -->
            <div id="section-dashboard" class="content-section">
                <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard General</h2>
                
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h4 id="totalUsers">{{ App\Models\User::count() }}</h4>
                                <p class="mb-0">Total Usuarios</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h4 id="pendingUsers">{{ App\Models\User::whereNull('role')->count() }}</h4>
                                <p class="mb-0">Sin Rol Asignado</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-user-graduate fa-2x mb-2"></i>
                                <h4 id="students">{{ App\Models\User::where('role', 'student')->count() }}</h4>
                                <p class="mb-0">Estudiantes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                                <h4 id="teachers">{{ App\Models\User::where('role', 'teacher')->count() }}</h4>
                                <p class="mb-0">Profesores</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accesos rápidos -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('users.index') }}" class="btn btn-primary">
                                        <i class="fas fa-users me-2"></i>Ver Todos los Usuarios
                                    </a>
                                    <a href="{{ route('users.pending') }}" class="btn btn-warning">
                                        <i class="fas fa-clock me-2"></i>Usuarios Pendientes de Rol
                                    </a>
                                    <a href="{{ route('users.create') }}" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>Crear Nuevo Usuario
                                    </a>
                                    <a href="{{ route('users.export') }}" class="btn btn-info">
                                        <i class="fas fa-download me-2"></i>Exportar Usuarios
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Distribución de Roles</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="roleChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección Gestión de Usuarios -->
            <div id="section-users" class="content-section d-none">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-users me-2"></i>Gestión de Usuarios</h2>
                    <a href="{{ route('users.index') }}" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i>Ver Página Completa
                    </a>
                </div>

                <div class="row">
                    <!-- Resumen rápido -->
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <h3 class="text-primary">{{ App\Models\User::count() }}</h3>
                                        <p class="mb-0">Total de Usuarios</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h3 class="text-success">{{ App\Models\User::whereNotNull('role')->count() }}</h3>
                                        <p class="mb-0">Con Rol Asignado</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h3 class="text-warning">{{ App\Models\User::whereNull('role')->count() }}</h3>
                                        <p class="mb-0">Pendientes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones de gestión -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Gestión General</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action">
                                        <i class="fas fa-list me-2 text-primary"></i>
                                        <strong>Ver Todos los Usuarios</strong>
                                        <br><small class="text-muted">Lista completa con filtros y búsqueda</small>
                                    </a>
                                    <a href="{{ route('users.create') }}" class="list-group-item list-group-item-action">
                                        <i class="fas fa-user-plus me-2 text-success"></i>
                                        <strong>Crear Nuevo Usuario</strong>
                                        <br><small class="text-muted">Agregar usuario con rol específico</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Asignación de Roles</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <a href="{{ route('users.pending') }}" class="list-group-item list-group-item-action">
                                        <i class="fas fa-clock me-2 text-warning"></i>
                                        <strong>Usuarios Pendientes</strong>
                                        <br><small class="text-muted">Asignar roles a nuevos usuarios</small>
                                    </a>
                                    <a href="{{ route('users.index') }}?role=" class="list-group-item list-group-item-action">
                                        <i class="fas fa-user-tag me-2 text-info"></i>
                                        <strong>Gestionar Roles</strong>
                                        <br><small class="text-muted">Cambiar roles existentes</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección Asignación de Roles -->
            <div id="section-roles" class="content-section d-none">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-user-tag me-2"></i>Asignación de Roles</h2>
                    <a href="{{ route('users.pending') }}" class="btn btn-warning">
                        <i class="fas fa-clock me-1"></i>Ver Usuarios Pendientes
                    </a>
                </div>

                <div class="row">
                    <!-- Roles disponibles -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Roles del Sistema</h5>
                            </div>
                            <div class="card-body">
                                <div class="role-item mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <span class="badge bg-danger">Admin</span>
                                            </h6>
                                            <small class="text-muted">Acceso completo al sistema</small>
                                        </div>
                                        <span class="badge bg-secondary">{{ App\Models\User::where('role', 'admin')->count() }}</span>
                                    </div>
                                </div>

                                <div class="role-item mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <span class="badge bg-success">Teacher</span>
                                            </h6>
                                            <small class="text-muted">Crear y gestionar cursos</small>
                                        </div>
                                        <span class="badge bg-secondary">{{ App\Models\User::where('role', 'teacher')->count() }}</span>
                                    </div>
                                </div>

                                <div class="role-item mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <span class="badge bg-primary">Student</span>
                                            </h6>
                                            <small class="text-muted">Acceso a cursos asignados</small>
                                        </div>
                                        <span class="badge bg-secondary">{{ App\Models\User::where('role', 'student')->count() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones rápidas para roles -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Herramientas de Asignación</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <i class="fas fa-users-cog fa-3x text-primary mb-3"></i>
                                                <h5>Asignación Individual</h5>
                                                <p class="text-muted">Asigna roles uno por uno desde la lista de usuarios</p>
                                                <a href="{{ route('users.index') }}" class="btn btn-primary">
                                                    <i class="fas fa-arrow-right me-1"></i>Ir a Lista
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <i class="fas fa-tasks fa-3x text-warning mb-3"></i>
                                                <h5>Asignación Masiva</h5>
                                                <p class="text-muted">Asigna roles a múltiples usuarios a la vez</p>
                                                <a href="{{ route('users.pending') }}" class="btn btn-warning">
                                                    <i class="fas fa-arrow-right me-1"></i>Usuarios Pendientes
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tutorial rápido -->
                                <div class="mt-4">
                                    <h6><i class="fas fa-question-circle me-1"></i>¿Cómo asignar roles?</h6>
                                    <ol class="small">
                                        <li><strong>Individual:</strong> Ve a la lista de usuarios → click en el dropdown del rol → selecciona el nuevo rol</li>
                                        <li><strong>Masiva:</strong> Ve a usuarios pendientes → selecciona usuarios → elige rol → asignar</li>
                                        <li><strong>Al crear:</strong> Cuando crees un nuevo usuario, selecciona el rol en el formulario</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección Reportes -->
            <div id="section-reports" class="content-section d-none">
                <h2><i class="fas fa-chart-bar me-2"></i>Reportes</h2>
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Sección de reportes en desarrollo</p>
                    </div>
                </div>
            </div>

            <!-- Sección Configuración -->
            <div id="section-settings" class="content-section d-none">
                <h2><i class="fas fa-cogs me-2"></i>Configuración</h2>
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Sección de configuración en desarrollo</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Navegación del sidebar
        document.querySelectorAll('[data-section]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                console.log('Click en sección:', this.dataset.section); // Debug
                
                // Remover clase active de todos los links
                document.querySelectorAll('[data-section]').forEach(l => l.classList.remove('active'));
                
                // Ocultar todas las secciones
                document.querySelectorAll('.content-section').forEach(section => {
                    section.classList.add('d-none');
                });
                
                // Mostrar sección seleccionada
                const sectionId = 'section-' + this.dataset.section;
                const targetSection = document.getElementById(sectionId);
                
                console.log('Buscando sección:', sectionId, targetSection); // Debug
                
                if (targetSection) {
                    targetSection.classList.remove('d-none');
                    // Activar link seleccionado
                    this.classList.add('active');
                } else {
                    console.error('No se encontró la sección:', sectionId);
                }
            });
        });

        // Gráfico de roles (solo si existe el elemento canvas)
        const chartCanvas = document.getElementById('roleChart');
        if (chartCanvas) {
            const ctx = chartCanvas.getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Administradores', 'Profesores', 'Estudiantes', 'Sin Asignar'],
                    datasets: [{
                        data: [
                            {{ App\Models\User::where('role', 'admin')->count() }},
                            {{ App\Models\User::where('role', 'teacher')->count() }},
                            {{ App\Models\User::where('role', 'student')->count() }},
                            {{ App\Models\User::whereNull('role')->count() }}
                        ],
                        backgroundColor: ['#dc3545', '#28a745', '#007bff', '#ffc107'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Función para mostrar sección específica (útil para debugging)
        window.showSection = function(sectionName) {
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.add('d-none');
            });
            document.querySelectorAll('[data-section]').forEach(l => l.classList.remove('active'));
            
            const sectionId = 'section-' + sectionName;
            const targetSection = document.getElementById(sectionId);
            const targetLink = document.querySelector(`[data-section="${sectionName}"]`);
            
            if (targetSection && targetLink) {
                targetSection.classList.remove('d-none');
                targetLink.classList.add('active');
            }
        };
    });
</script>
@endpush

@push('styles')
<style>
    .content-section {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .role-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: box-shadow 0.3s ease;
    }

    .list-group-item-action:hover {
        transform: translateX(5px);
        transition: transform 0.2s ease;
    }
</style>
@endpush