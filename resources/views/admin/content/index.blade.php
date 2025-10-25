@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-book"></i> Biblioteca de Contenidos</h4>
                    <div>
                        <a href="{{ route('admin.content.create') }}" class="btn btn-light">
                            <i class="fas fa-plus"></i> Nuevo Contenido
                        </a>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                            <i class="fas fa-upload"></i> Carga Masiva
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filtros -->
                    <form method="GET" action="{{ route('admin.content.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Materia</label>
                                <select class="form-select" name="subject">
                                    <option value="">Todas las materias</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject }}" {{ request('subject') == $subject ? 'selected' : '' }}>
                                            {{ $subject }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tipo</label>
                                <select class="form-select" name="type">
                                    <option value="">Todos los tipos</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Buscar</label>
                                <input type="text" 
                                       class="form-control" 
                                       placeholder="Buscar contenido..." 
                                       name="search"
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary w-100" type="submit">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Materia</th>
                                    <th>Tipo</th>
                                    <th>Dificultad</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contents as $content)
                                <tr>
                                    <td>{{ $content->id }}</td>
                                    <td>
                                        <strong>{{ $content->title }}</strong>
                                        @if($content->content_url)
                                            <br><small class="text-muted">
                                                <i class="fas fa-link"></i> 
                                                <a href="{{ $content->content_url }}" target="_blank">Ver enlace</a>
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $content->subject_area }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $content->type }}</span>
                                    </td>
                                    <td>{{ $content->difficulty_level }}</td>
                                    <td>
                                        @if($content->active)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-warning">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.content.show', $content->id) }}" 
                                               class="btn btn-sm btn-info"
                                               title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.content.edit', $content->id) }}" 
                                               class="btn btn-sm btn-primary"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" 
                                                  action="{{ route('admin.content.destroy', $content->id) }}" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este contenido?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No hay contenidos disponibles</h5>
                                        <p class="text-muted">Comienza agregando tu primer contenido</p>
                                        <a href="{{ route('admin.content.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Crear Contenido
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($contents, 'links'))
                        <div class="mt-3">
                            {{ $contents->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para carga masiva -->
<div class="modal fade" id="bulkUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Carga Masiva de Contenidos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.content.bulk-upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Archivo CSV/Excel:</label>
                        <input type="file" class="form-control" name="file" accept=".csv,.xlsx" required>
                        <small class="form-text text-muted">
                            Sube un archivo CSV o Excel con los contenidos.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Subir Contenidos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
}
.table td {
    vertical-align: middle;
}
</style>
@endsection