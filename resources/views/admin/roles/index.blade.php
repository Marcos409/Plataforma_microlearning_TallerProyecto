@extends('layouts.app')

@section('title', 'Gestión de Roles')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-user-tag me-2"></i>Gestión de Roles</h2>
            <p class="text-muted">Asigna y administra roles de usuarios del sistema</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#massiveAssignModal">
                <i class="fas fa-users-cog me-1"></i>Asignación Masiva
            </button>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h4>{{ App\Models\User::count() }}</h4>
                    <p class="mb-0">Total Usuarios</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h4>{{ $usersWithoutRole }}</h4>
                    <p class="mb-0">Sin Rol Asignado</p>
                </div>
            </div>
        </div>
        @foreach($roles as $role)
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-user-shield fa-2x mb-2"></i>
                    <h4>{{ $role->users_count }}</h4>
                    <p class="mb-0">{{ $role->name }}s</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Gestión de Roles -->
    <div class="row">
        <!-- Lista de Roles -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Roles del Sistema</h5>
                </div>
                <div class="card-body">
                    @foreach($roles as $role)
                    <div class="role-item mb-3 p-3 border rounded" data-role-id="{{ $role->id }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    <span class="badge bg-{{ $role->name == 'Administrador' ? 'danger' : ($role->name == 'Docente' ? 'success' : 'primary') }}">
                                        {{ $role->name }}
                                    </span>
                                </h6>
                                <small class="text-muted">{{ $role->description }}</small>
                            </div>
                            <span class="badge bg-secondary">{{ $role->users_count }} usuarios</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Lista de Usuarios para Asignación -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Asignación Individual</h5>
                    <div class="mt-2">
                        <select class="form-select" id="userFilterRole">
                            <option value="">Todos los usuarios</option>
                            <option value="null">Sin rol asignado</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Carrera</th>
                                    <th>Rol Actual</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <!-- Los usuarios se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                    <div id="loadingSpinner" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Asignación Masiva -->
<div class="modal fade" id="massiveAssignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users-cog me-2"></i>Asignación Masiva de Roles</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="massiveAssignForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Rol a Asignar</label>
                        <select class="form-select" name="role_id" required>
                            <option value="">Seleccione un rol...</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usuarios a Asignar</label>
                        <div class="border p-3 max-height-300 overflow-auto">
                            <div class="mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAllUsers">
                                    <label class="form-check-label fw-bold" for="selectAllUsers">
                                        Seleccionar todos los usuarios sin rol
                                    </label>
                                </div>
                            </div>
                            <hr>
                            <div id="usersForMassiveAssign">
                                <!-- Se cargarán usuarios sin rol -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="executeMassiveAssign">
                    <i class="fas fa-save me-1"></i>Asignar Roles
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmAction">Confirmar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentAction = null;
    let currentData = {};
    
    // Cargar usuarios al inicio
    loadUsers();
    loadUsersForMassiveAssign();
    
    // Filtrar usuarios por rol
    document.getElementById('userFilterRole').addEventListener('change', function() {
        loadUsers(this.value);
    });
    
    // Seleccionar todos los usuarios en asignación masiva
    document.getElementById('selectAllUsers').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('#usersForMassiveAssign input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
    
    // Ejecutar asignación masiva
    document.getElementById('executeMassiveAssign').addEventListener('click', function() {
        const form = document.getElementById('massiveAssignForm');
        const formData = new FormData(form);
        const selectedUsers = [];
        
        document.querySelectorAll('#usersForMassiveAssign input[type="checkbox"]:checked').forEach(checkbox => {
            selectedUsers.push(checkbox.value);
        });
        
        if (selectedUsers.length === 0) {
            showAlert('Debe seleccionar al menos un usuario', 'warning');
            return;
        }
        
        if (!formData.get('role_id')) {
            showAlert('Debe seleccionar un rol', 'warning');
            return;
        }
        
        currentAction = 'massiveAssign';
        currentData = {
            user_ids: selectedUsers,
            role_id: formData.get('role_id')
        };
        
        document.getElementById('confirmMessage').textContent = 
            `¿Está seguro de asignar el rol seleccionado a ${selectedUsers.length} usuario(s)?`;
        
        new bootstrap.Modal(document.getElementById('confirmModal')).show();
        bootstrap.Modal.getInstance(document.getElementById('massiveAssignModal')).hide();
    });
    
    // Confirmar acción
    document.getElementById('confirmAction').addEventListener('click', function() {
        if (currentAction === 'assignRole') {
            assignRole(currentData.userId, currentData.roleId);
        } else if (currentAction === 'removeRole') {
            removeRole(currentData.userId);
        } else if (currentAction === 'massiveAssign') {
            assignMassiveRoles(currentData.user_ids, currentData.role_id);
        }
        
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
        currentAction = null;
        currentData = {};
    });
    
    // Funciones principales
    function loadUsers(roleFilter = '') {
        showLoading(true);
        
        fetch(`/api/users?role=${roleFilter}`)
            .then(response => response.json())
            .then(data => {
                renderUsersTable(data.users || data);
                showLoading(false);
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error al cargar usuarios', 'danger');
                showLoading(false);
            });
    }
    
    function loadUsersForMassiveAssign() {
        fetch('/api/users?role=null')
            .then(response => response.json())
            .then(data => {
                renderUsersForMassiveAssign(data.users || data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    function renderUsersTable(users) {
        const tbody = document.getElementById('usersTableBody');
        tbody.innerHTML = '';
        
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay usuarios para mostrar</td></tr>';
            return;
        }
        
        users.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            ${user.name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="fw-bold">${user.name}</div>
                            ${user.student_code ? `<small class="text-muted">${user.student_code}</small>` : ''}
                        </div>
                    </div>
                </td>
                <td>${user.email}</td>
                <td>${user.career || '-'}</td>
                <td>
                    <span class="badge bg-${getRoleBadgeColor(user.role_name)}">
                        ${user.role_name || 'Sin asignar'}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <select class="form-select form-select-sm" style="max-width: 150px;" onchange="handleRoleChange(${user.id}, this.value)">
                            <option value="">Cambiar rol...</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                            <option value="remove">Remover rol</option>
                        </select>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    function renderUsersForMassiveAssign(users) {
        const container = document.getElementById('usersForMassiveAssign');
        container.innerHTML = '';
        
        if (users.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">No hay usuarios sin rol asignado</p>';
            return;
        }
        
        users.forEach(user => {
            const div = document.createElement('div');
            div.className = 'form-check mb-2';
            div.innerHTML = `
                <input class="form-check-input" type="checkbox" value="${user.id}" id="user_${user.id}">
                <label class="form-check-label" for="user_${user.id}">
                    <strong>${user.name}</strong> - ${user.email}
                    ${user.career ? `<br><small class="text-muted">${user.career}</small>` : ''}
                </label>
            `;
            container.appendChild(div);
        });
    }
    
    function assignRole(userId, roleId) {
        showLoading(true);
        
        fetch('/roles/assign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ user_id: userId, role_id: roleId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                loadUsers();
                updateStats();
            } else {
                showAlert(data.message, 'danger');
            }
            showLoading(false);
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error al asignar rol', 'danger');
            showLoading(false);
        });
    }
    
    function removeRole(userId) {
        showLoading(true);
        
        fetch('/roles/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                loadUsers();
                updateStats();
            } else {
                showAlert(data.message, 'danger');
            }
            showLoading(false);
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error al remover rol', 'danger');
            showLoading(false);
        });
    }
    
    function assignMassiveRoles(userIds, roleId) {
        showLoading(true);
        
        fetch('/roles/assign-massive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ user_ids: userIds, role_id: roleId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                loadUsers();
                loadUsersForMassiveAssign();
                updateStats();
                // Resetear formulario
                document.getElementById('massiveAssignForm').reset();
                document.getElementById('selectAllUsers').checked = false;
            } else {
                showAlert(data.message, 'danger');
            }
            showLoading(false);
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error en asignación masiva', 'danger');
            showLoading(false);
        });
    }
    
    // Funciones auxiliares
    function getRoleBadgeColor(roleName) {
        if (!roleName || roleName === 'Sin asignar') return 'secondary';
        if (roleName.toLowerCase().includes('admin')) return 'danger';
        if (roleName.toLowerCase().includes('docente')) return 'success';
        if (roleName.toLowerCase().includes('estudiante')) return 'primary';
        return 'info';
    }
    
    function showLoading(show) {
        const spinner = document.getElementById('loadingSpinner');
        const table = document.getElementById('usersTable');
        
        if (show) {
            spinner.classList.remove('d-none');
            table.style.opacity = '0.5';
        } else {
            spinner.classList.add('d-none');
            table.style.opacity = '1';
        }
    }
    
    function showAlert(message, type) {
        // Crear alerta Bootstrap
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Remover automáticamente después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    function updateStats() {
        // Recargar estadísticas
        window.location.reload();
    }
    
    // Función global para manejar cambios de rol
    window.handleRoleChange = function(userId, action) {
        if (!action) return;
        
        if (action === 'remove') {
            currentAction = 'removeRole';
            currentData = { userId: userId };
            document.getElementById('confirmMessage').textContent = 
                '¿Está seguro de remover el rol de este usuario?';
            new bootstrap.Modal(document.getElementById('confirmModal')).show();
        } else {
            currentAction = 'assignRole';
            currentData = { userId: userId, roleId: action };
            document.getElementById('confirmMessage').textContent = 
                '¿Está seguro de cambiar el rol de este usuario?';
            new bootstrap.Modal(document.getElementById('confirmModal')).show();
        }
    };
});
</script>

<style>
.max-height-300 {
    max-height: 300px;
}

.avatar {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
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

.table-responsive {
    border-radius: 0.375rem;
}

.btn-group-sm .form-select {
    font-size: 0.875rem;
}
</style>