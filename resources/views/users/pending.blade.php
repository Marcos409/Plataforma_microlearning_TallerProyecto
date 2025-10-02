@extends('layouts.app')

@section('title', 'Usuarios Pendientes de Rol')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="fas fa-clock me-2 text-warning"></i>Usuarios Pendientes de Rol
                    <span class="badge bg-warning text-dark ms-2">{{ $users->total() }}</span>
                </h1>
                <div>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success me-2">
                        <i class="fas fa-plus me-1"></i>Nuevo Usuario
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Todos los Usuarios
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

            @if($users->count() > 0)
                <!-- Información -->
                <div class="alert alert-info mb-4">
                    <h6><i class="fas fa-info-circle me-2"></i>Información</h6>
                    <p class="mb-0">Los usuarios mostrados a continuación se han registrado pero aún no tienen un rol asignado. Asigna roles para que puedan acceder a las funcionalidades correspondientes del sistema.</p>
                </div>

                <!-- Tabla de usuarios pendientes -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-1"></i>Usuarios sin Rol Asignado
                            </h5>
                            <small class="text-muted">{{ $users->count() }} de {{ $users->total() }} usuarios</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Fecha de Registro</th>
                                        <th>Estado</th>
                                        <th>Asignar Rol</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr id="user-{{ $user->id }}">
                                            <td>
                                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" 
                                                       class="form-check-input user-checkbox">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-3">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $user->name }}</h6>
                                                        <small class="text-muted">ID: #{{ $user->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                                    {{ $user->email }}
                                                </a>
                                            </td>
                                            <td>
                                                <span title="{{ $user->created_at->format('d/m/Y H:i:s') }}">
                                                    {{ $user->created_at->diffForHumans() }}
                                                </span>
                                                <br>
                                                <small class="text-muted">{{ $user->created_at->format('d/m/Y') }}</small>
                                            </td>
                                            <td>
                                                @if($user->email_verified_at)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Verificado
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>Pendiente
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @foreach($roles as $role)
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-primary assign-role-btn"
                                                                data-user-id="{{ $user->id }}" 
                                                                data-role="{{ $role }}"
                                                                title="Asignar rol de {{ ucfirst($role) }}">
                                                            {{ ucfirst($role) }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.users.show', $user) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Editar usuario">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger delete-user-btn" 
                                                            data-user-id="{{ $user->id }}"
                                                            data-user-name="{{ $user->name }}"
                                                            title="Eliminar usuario">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        @if($users->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $users->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Panel de acciones masivas -->
                <div class="card mt-3" id="bulkActions" style="display: none;">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-users-cog me-1"></i>Asignación Masiva de Roles</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.users.bulk-assign-role') }}" id="bulkForm">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Asignar rol a usuarios seleccionados:</label>
                                    <select name="role" class="form-select" required>
                                        <option value="">Seleccionar rol...</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-check me-1"></i>Asignar Rol
                                    </button>
                                    <span class="text-muted" id="selectedCount">0 usuarios seleccionados</span>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">
                                        <i class="fas fa-times me-1"></i>Limpiar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h4>{{ $users->total() }}</h4>
                                <p class="mb-0">Usuarios Pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar fa-2x mb-2"></i>
                                <h4>{{ $users->where('created_at', '>=', now()->subDays(7))->count() }}</h4>
                                <p class="mb-0">Esta Semana</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope fa-2x mb-2"></i>
                                <h4>{{ $users->whereNull('email_verified_at')->count() }}</h4>
                                <p class="mb-0">Sin Verificar Email</p>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <!-- Estado vacío -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                        <h3 class="text-muted">¡Excelente!</h3>
                        <p class="text-muted mb-4">No hay usuarios pendientes de asignación de rol.</p>
                        <a href="{{ route('users.index') }}" class="btn btn-primary">
                            <i class="fas fa-users me-1"></i>Ver Todos los Usuarios
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Eliminar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar al usuario <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar Usuario</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Selección múltiple
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const selected = document.querySelectorAll('.user-checkbox:checked');
        const bulkPanel = document.getElementById('bulkActions');
        const countSpan = document.getElementById('selectedCount');
        
        if (selected.length > 0) {
            bulkPanel.style.display = 'block';
            countSpan.textContent = `${selected.length} usuarios seleccionados`;
            
            // Agregar inputs hidden con los IDs seleccionados
            const form = document.getElementById('bulkForm');
            const existingInputs = form.querySelectorAll('input[name="user_ids[]"]');
            existingInputs.forEach(input => input.remove());
            
            selected.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });
        } else {
            bulkPanel.style.display = 'none';
        }
    }

    function clearSelection() {
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('selectAll').checked = false;
        updateBulkActions();
    }

    // Asignación individual de roles
    document.querySelectorAll('.assign-role-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const role = this.dataset.role;
            const userRow = document.getElementById(`user-${userId}`);
            
            if (confirm(`¿Asignar el rol de "${role}" a este usuario?`)) {
                // Deshabilitar botón durante la petición
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Asignando...';
                
                fetch(`/users/${userId}/role`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ role: role })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mostrar mensaje de éxito
                        showAlert('success', `Rol "${role}" asignado correctamente`);
                        
                        // Remover la fila de la tabla con animación
                        userRow.style.transition = 'all 0.5s ease';
                        userRow.style.opacity = '0';
                        userRow.style.transform = 'translateX(100%)';
                        
                        setTimeout(() => {
                            userRow.remove();
                            // Actualizar contador si existe
                            updatePendingCount();
                        }, 500);
                    } else {
                        showAlert('error', 'Error al asignar el rol');
                        this.disabled = false;
                        this.innerHTML = role;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Error al asignar el rol');
                    this.disabled = false;
                    this.innerHTML = role;
                });
            }
        });
    });

    // Eliminar usuarios
    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('deleteForm').action = `/users/${userId}`;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show`;
        alert.innerHTML = `
            <i class="fas ${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container-fluid');
        container.insertBefore(alert, container.firstChild);
        
        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            alert.remove();
        }, 3000);
    }

    function updatePendingCount() {
        const remainingRows = document.querySelectorAll('tbody tr').length;
        const badge = document.querySelector('.badge.bg-warning');
        if (badge) {
            badge.textContent = remainingRows;
        }
        
        // Si no quedan usuarios pendientes, mostrar mensaje de éxito
        if (remainingRows === 0) {
            location.reload();
        }
    }
</script>
@endpush

@push('styles')
<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #007bff;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        text-transform: uppercase;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .assign-role-btn {
        transition: all 0.3s ease;
    }

    .assign-role-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    #bulkActions {
        border: 2px solid #007bff;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush