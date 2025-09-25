@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Biblioteca de Contenidos</h4>
                    <div>
                        <a href="{{ route('admin.content.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Contenido
                        </a>
                        <button class="btn btn-success" data-toggle="modal" data-target="#bulkUploadModal">
                            <i class="fas fa-upload"></i> Carga Masiva
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" name="subject">
                                <option value="">Todas las materias</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject }}">{{ $subject }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="type">
                                <option value="">Todos los tipos</option>
                                @foreach($types as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" placeholder="Buscar contenido..." name="search">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
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
                                @foreach($contents as $content)
                                <tr>
                                    <td>{{ $content->id }}</td>
                                    <td>{{ $content->title }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $content->subject_area }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $content->type }}</span>
                                    </td>
                                    <td>{{ $content->difficulty_level }}</td>
                                    <td>
                                        @if($content->active)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-warning">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.content.show', $content->id) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.content.edit', $content->id) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.content.destroy', $content->id) }}" 
                                                  style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('¿Estás seguro?')">
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
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.content.bulk-upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Archivo CSV/Excel:</label>
                        <input type="file" class="form-control-file" name="file" accept=".csv,.xlsx">
                        <small class="form-text text-muted">
                            Sube un archivo CSV o Excel con los contenidos.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Subir Contenidos</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection