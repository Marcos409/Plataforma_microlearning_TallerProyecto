@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Gestión de Diagnósticos
                    </h4>
                    <a href="{{ route('admin.diagnostics.create') }}" class="btn btn-light">
                        <i class="fas fa-plus me-1"></i> Nuevo Diagnóstico
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($diagnostics->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Descripción</th>
                                        <th>Materia</th>
                                        <th>Preguntas</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($diagnostics as $diagnostic)
                                    <tr>
                                        <td>{{ $diagnostic->id }}</td>
                                        <td>
                                            <strong>{{ $diagnostic->title }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ Str::limit($diagnostic->description ?? 'Sin descripción', 50) }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $diagnostic->subject_area ?? $diagnostic->subject ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">
                                                {{ $diagnostic->questions_count ?? $diagnostic->questions()->count() ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $diagnostic->created_at ? $diagnostic->created_at->format('d/m/Y') : 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.diagnostics.show', $diagnostic->id) }}" 
                                                   class="btn btn-sm btn-outline-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.diagnostics.edit', $diagnostic->id) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('admin.diagnostics.destroy', $diagnostic->id) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Eliminar"
                                                            onclick="return confirm('¿Estás seguro de eliminar este diagnóstico?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($diagnostics, 'links'))
                            <div class="mt-3">
                                {{ $diagnostics->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay diagnósticos creados</h5>
                            <p class="text-muted">Comienza creando tu primer diagnóstico</p>
                            <a href="{{ route('admin.diagnostics.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Crear Diagnóstico
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection