@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-users me-2"></i>Gestión de Usuarios</h1>
                <div>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Nuevo Usuario
                    </a>
                    <a href="{{ route('admin.users.pending') }}" class="btn btn-warning">
                        <i class="fas fa-clock me-1"></i>Usuarios Pendientes
                    </a>
                    <a href="{{ route('admin.users.export') }}" class="btn btn-info">
                        <i class="fas fa-download me-1"></i>Exportar
                    </a>
                    
                </div>
            </div>

            <!-- Alertas -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.users.index') }}">
                        <div class="row">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Buscar por nombre o email..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="role" class="form-select">
                                    <option value="">Todos los roles</option>
                                    <option value="1" {{ request('role') == '1' ? 'selected' : '' }}>Administrador</option>
                                    <option value="2" {{ request('role') == '2' ? 'selected' : '' }}>Docente</option>
                                    <option value="3" {{ request('role') == '3' ? 'selected' : '' }}>Estudiante</option>
                                    <option value="4" {{ request('role') == '4' ? 'selected' : '' }}>Sin Rol</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="active" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Activos</option>
                                    <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Registro</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2 bg-{{ $user->role_id == 1 ? 'danger' : ($user->role_id == 2 ? 'success' : 'primary') }}">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $user->name }}</strong>
                                                    @if($user->student_code)
                                                        <br><small class="text-muted">Código: {{ $user->student_code }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->role)
                                                @if($user->role->name == 'Administrador')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-user-shield me-1"></i>Admin
                                                    </span>
                                                @elseif($user->role->name == 'Docente')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-chalkboard-teacher me-1"></i>Teacher
                                                    </span>
                                                @elseif($user->role->name == 'Estudiante')
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-user-graduate me-1"></i>Student
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $user->role->name }}</span>
                                                @endif
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>Sin rol
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $user->created_at->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            @if($user->active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Activo
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-ban me-1"></i>Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.users.show', $user) }}" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.users.edit', $user) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('admin.users.destroy', $user) }}" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Estás seguro de eliminar a {{ $user->name }}? Esta acción no se puede deshacer.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Eliminar"
                                                            {{ $user->id == 1 ? 'disabled' : '' }}>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-users fa-4x text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0">No hay usuarios que coincidan con los filtros</p>
                                            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-link">Limpiar filtros</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($users->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <p class="text-muted mb-0">
                                    Mostrando {{ $users->firstItem() }} - {{ $users->lastItem() }} de {{ $users->total() }} usuarios
                                </p>
                            </div>
                            <div>
                                {{ $users->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .table th {
        border-top: none;
        font-weight: 600;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-weight: 500;
        padding: 0.375em 0.75em;
    }
</style>
@endsection