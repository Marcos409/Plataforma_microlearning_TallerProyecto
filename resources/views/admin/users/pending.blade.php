<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Usuarios Pendientes de Rol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-clock me-2 text-warning"></i>Usuarios Pendientes de Rol</h1>
                    <div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Todos los Usuarios
                        </a>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>Nuevo Usuario
                        </a>
                        <a href="{{ route('admin.users.export') }}" class="btn btn-info">
                            <i class="fas fa-download me-1"></i>Exportar CSV
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

                <!-- Información de usuarios pendientes -->
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Usuarios sin rol asignado:</strong> Estos usuarios necesitan que se les asigne un rol para poder usar el sistema correctamente.
                </div>

                <!-- Tabla de usuarios pendientes -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-warning">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Fecha Registro</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" 
                                                       class="form-check-input user-checkbox">
                                            </td>
                                            <td>{{ $user->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </div>
                                                    {{ $user->name }}
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>Sin rol
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.users.show', $user->id) }}" 
                                                       class="btn btn-sm btn-outline-info" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.users.edit', $user->id) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.users.destroy', $user->id) }}" 
                                                            method="POST" 
                                                            class="d-inline"
                                                            onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-delete" title="Eliminar">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                    </form>
                                                    <!-- Botón rápido para asignar rol -->
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-warning dropdown-toggle" 
                                                                type="button" data-bs-toggle="dropdown" title="Asignar rol">
                                                            <i class="fas fa-user-tag"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            @foreach($roles as $role)
                                                                <li>
                                                                    <a class="dropdown-item role-assign" 
                                                                       href="#" 
                                                                       data-user-id="{{ $user->id }}" 
                                                                       data-role-id="{{ $role->id }}"
                                                                       data-role-name="{{ $role->name }}">
                                                                        {{ $role->name }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                                <p class="text-muted mb-0">¡Excelente! No hay usuarios pendientes de rol.</p>
                                                <p class="text-muted">Todos los usuarios tienen roles asignados.</p>
                                            </td>
                                        </tr>
                                    @endforelse
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

                <!-- Panel de asignación masiva -->
                @if($users->count() > 0)
                <div class="card mt-3" id="bulkActions" style="display: none;">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-users-cog me-2"></i>Asignación Masiva de Roles</h5>
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
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-users-cog me-1"></i>Asignar Roles
                                    </button>
                                    <span class="ms-2 text-muted" id="selectedCount">0 usuarios seleccionados</span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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

        // Asignación individual de rol
        document.querySelectorAll('.role-assign').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.dataset.userId;
                const roleId = this.dataset.roleId;
                const roleName = this.dataset.roleName;
                
                if (confirm(`¿Asignar el rol "${roleName}" a este usuario?`)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/users/${userId}/assign-role`;
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="role_id" value="${roleId}">
                    `;
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
        
        
    </script>

<style>
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #ffc107;
        color: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        text-transform: uppercase;
    }

    .table { 
        min-width: 1200px;
    }
    
    .table th { 
        border-top: none;
        white-space: nowrap;
        padding: 12px 15px;
    }
    
    .table td {
        vertical-align: middle;
        padding: 12px 15px;
    }

    .btn-group .btn { 
        margin-right: 2px; 
    }
    
    .btn-group .btn:last-child { 
        margin-right: 0; 
    }

    .table-warning th {
        background-color: #fff3cd;
        border-color: #ffeaa7;
    }

    /* Efecto hover para el botón de eliminar */
    .btn-outline-danger {
        transition: all 0.3s ease;
    }

    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }

    /* Mejorar el ancho de la tabla */
    .table-responsive {
        min-height: 400px;
        overflow-x: auto;
        padding-bottom: 20px;
    }

    /* Asegurar que las columnas no se compriman */
    .table td:nth-child(3) { /* Nombre */
        min-width: 200px;
    }

    .table td:nth-child(4) { /* Email */
        min-width: 250px;
    }

    .table td:nth-child(7) { /* Acciones */
        min-width: 280px;
        white-space: nowrap;
    }

    /* Mejorar el dropdown */
    .dropdown-menu {
        min-width: 180px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .dropdown-item {
        padding: 8px 16px;
        transition: background-color 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    /* Efectos hover mejorados */
    .btn-outline-info:hover,
    .btn-outline-primary:hover,
    .btn-outline-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }
</style>
</body>
</html>