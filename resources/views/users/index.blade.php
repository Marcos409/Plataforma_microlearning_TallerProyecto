<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-users me-2"></i>Gestión de Usuarios</h1>
                    <div>
                        <a href="{{ route('users.create') }}" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>Nuevo Usuario
                        </a>
                        <a href="{{ route('users.pending') }}" class="btn btn-warning">
                            <i class="fas fa-clock me-1"></i>Usuarios Pendientes
                        </a>
                        <a href="{{ route('users.export') }}" class="btn btn-info">
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

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('users.index') }}">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Buscar por nombre o email..." 
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-4">
                                    <select name="role" class="form-select">
                                        <option value="">Todos los roles</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                                                {{ ucfirst($role) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i>Filtrar
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
                                        <th>
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
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
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                            type="button" data-bs-toggle="dropdown">
                                                        <span class="role-badge role-{{ $user->role ?? 'none' }}">
                                                            {{ $user->role ? ucfirst($user->role) : 'Sin asignar' }}
                                                        </span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        @foreach($roles as $role)
                                                            <li>
                                                                <a class="dropdown-item role-change" 
                                                                   href="#" 
                                                                   data-user-id="{{ $user->id }}" 
                                                                   data-role="{{ $role }}">
                                                                    {{ ucfirst($role) }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </td>
                                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                @if($user->email_verified_at)
                                                    <span class="badge bg-success">Verificado</span>
                                                @else
                                                    <span class="badge bg-warning">Pendiente</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('users.show', $user) }}" 
                                                       class="btn btn-sm btn-outline-info" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('users.edit', $user) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('users.destroy', $user) }}" 
                                                          class="d-inline" onsubmit="return confirm('¿Estás seguro?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No hay usuarios registrados</p>
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

                <!-- Panel de acciones masivas -->
                <div class="card mt-3" id="bulkActions" style="display: none;">
                    <div class="card-body">
                        <form method="POST" action="{{ route('users.bulk-assign-role') }}" id="bulkForm">
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
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-users-cog me-1"></i>Asignar Rol
                                    </button>
                                    <span class="ms-2 text-muted" id="selectedCount">0 usuarios seleccionados</span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
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

        // Cambio de rol individual
        document.querySelectorAll('.role-change').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.dataset.userId;
                const role = this.dataset.role;
                
                if (confirm(`¿Cambiar el rol a "${role}"?`)) {
                    fetch(`/users/${userId}/role`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ role: role })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error al actualizar el rol');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al actualizar el rol');
                    });
                }
            });
        });
    </script>

    <style>
        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.875em;
        }

        .role-admin { background-color: #dc3545; color: white; }
        .role-teacher { background-color: #28a745; color: white; }
        .role-student { background-color: #007bff; color: white; }
        .role-none { background-color: #6c757d; color: white; }

        .table th { border-top: none; }
        .btn-group .btn { margin-right: 2px; }
        .btn-group .btn:last-child { margin-right: 0; }
    </style>
</body>
</html>