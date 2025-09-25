@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Gestión de Diagnósticos</h4>
                    <a href="{{ route('admin.diagnostics.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Diagnóstico
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Materia</th>
                                    <th>Preguntas</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($diagnostics as $diagnostic)
                                <tr>
                                    <td>{{ $diagnostic->id }}</td>
                                    <td>{{ $diagnostic->title }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $diagnostic->subject }}</span>
                                    </td>
                                    <td>{{ $diagnostic->questions_count }}</td>
                                    <td>
                                        @if($diagnostic->active)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.diagnostics.show', $diagnostic->id) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.diagnostics.questions.index', $diagnostic->id) }}" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-question"></i>
                                            </a>
                                            <a href="{{ route('admin.diagnostics.edit', $diagnostic->id) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.diagnostics.destroy', $diagnostic->id) }}" 
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
@endsection