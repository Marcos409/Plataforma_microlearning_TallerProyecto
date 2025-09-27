@extends('layouts.app')

@section('title', 'Detalles del Usuario')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <!-- Perfil del usuario -->
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar-circle-xl mb-3">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    <span class="badge fs-6 role-{{ $user->role ?? 'none' }} mb-3">
                        {{ $user->role ? ucfirst($user->role) : 'Sin rol asignado' }}
                    </span>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>Editar Usuario
                        </a>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver a Lista
                        </a>
                    </div>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-1"></i>Estadísticas</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-primary mb-0">0</h5>
                                <small class="text-muted">Cursos</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-success mb-0">0</h5>
                            <small class="text-muted">Completados</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user me-2"></i>Detalles del Usuario</h1>
                <div class="btn-group" role="group">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash me-1"></i>Eliminar
                    </button>
                </div>
            </div>

            <!-- Información general -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-1"></i>Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">ID:</td>
                                    <td>#{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Nombre:</td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Email:</td>
                                    <td>
                                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                        @if($user->email_verified_at)
                                            <i class="fas fa-check-circle text-success ms-1" title="Verificado"></i>
                                        @else
                                            <i class="fas fa-exclamation-circle text-warning ms-1" title="No verificado"></i>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Rol:</td>
                                    <td>
                                        <span class="badge role-{{ $user->role ?? 'none' }}">
                                            {{ $user->role ? ucfirst($user->role) : 'Sin asignar' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Fecha de registro:</td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Última actualización:</td>
                                    <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Email verificado:</td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">{{ $user->email_verified_at->format('d/m/Y H:i') }}</span>
                                        @else
                                            <span class="badge bg-warning">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Estado:</td>
                                    <td>
                                        <span class="badge bg-success">Activo</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actividad reciente -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-1"></i>Actividad Reciente</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Usuario registrado</h6>
                                <p class="text-muted mb-0">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @if($user->email_verified_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Email verificado</h6>
                                <p class="text-muted mb-0">{{ $user->email_verified_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        @if($user->role)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Rol asignado: {{ ucfirst($user->role) }}</h6>
                                <p class="text-muted mb-0">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cursos y progreso (placeholder) -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap me-1"></i>Cursos</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay cursos asignados aún</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Eliminar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar al usuario <strong>{{ $user->name }}</strong>?</p>
                <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar Usuario</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-circle-xl {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 28px;
        text-transform: uppercase;
        margin: 0 auto;
    }

    .role-admin { background-color: #dc3545; color: white; }
    .role-teacher { background-color: #28a745; color: white; }
    .role-student { background-color: #007bff; color: white; }
    .role-none { background-color: #6c757d; color: white; }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -23px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 3px solid #007bff;
    }
</style>
@endpush