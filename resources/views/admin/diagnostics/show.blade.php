@extends('layouts.app')

@section('title', 'Detalle del Diagnóstico')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-clipboard-list me-2"></i>{{ $diagnostic->title }}</h2>
            <p class="text-muted mb-0">
                <span class="badge bg-primary">{{ $diagnostic->subject_area }}</span>
                <span class="badge bg-{{ $diagnostic->active ? 'success' : 'secondary' }} ms-2">
                    {{ $diagnostic->active ? 'Activo' : 'Inactivo' }}
                </span>
            </p>
        </div>
        <div>
            <a href="{{ route('admin.diagnostics.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
            <a href="{{ route('admin.diagnostics.edit', $diagnostic) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Editar
            </a>
            <a href="{{ route('admin.diagnostics.questions.index', $diagnostic) }}" class="btn btn-success">
                <i class="fas fa-question-circle me-1"></i>Gestionar Preguntas
            </a>
        </div>
    </div>

    <!-- Información del Diagnóstico -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Título:</strong>
                            <p>{{ $diagnostic->title }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Área de Materia:</strong>
                            <p>{{ $diagnostic->subject_area }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Puntaje Mínimo:</strong>
                            <p>{{ $diagnostic->passing_score }}%</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Estado:</strong>
                            <p>
                                <span class="badge bg-{{ $diagnostic->active ? 'success' : 'secondary' }}">
                                    {{ $diagnostic->active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Descripción:</strong>
                        <p>{{ $diagnostic->description ?: 'Sin descripción' }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>Fecha de Creación:</strong>
                            <p>{{ $diagnostic->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Última Actualización:</strong>
                            <p>{{ $diagnostic->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="col-md-4">
            <div class="card bg-primary text-white mb-3">
                <div class="card-body text-center">
                    <i class="fas fa-question-circle fa-3x mb-2"></i>
                    <h3>{{ $diagnostic->total_questions ?? $diagnostic->questions->count() }}</h3>
                    <p class="mb-0">Total de Preguntas</p>
                </div>
            </div>

            <div class="card bg-success text-white mb-3">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x mb-2"></i>
                    <h3>0</h3>
                    <p class="mb-0">Estudiantes Evaluados</p>
                </div>
            </div>

            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x mb-2"></i>
                    <h3>0%</h3>
                    <p class="mb-0">Tasa de Aprobación</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Preguntas -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Preguntas del Diagnóstico</h5>
            <a href="{{ route('admin.diagnostics.questions.create', $diagnostic) }}" class="btn btn-sm btn-success">
                <i class="fas fa-plus me-1"></i>Agregar Pregunta
            </a>
        </div>
        <div class="card-body">
            @if($diagnostic->questions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="50%">Pregunta</th>
                                <th width="15%">Tema</th>
                                <th width="15%">Dificultad</th>
                                <th width="15%" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($diagnostic->questions as $index => $question)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ Str::limit($question->question_text, 80) }}</td>
                                    <td>{{ $question->topic }}</td>
                                    <td>
                                        <span class="badge bg-{{ $question->difficulty_level == 1 ? 'success' : ($question->difficulty_level == 2 ? 'warning' : 'danger') }}">
                                            {{ $question->difficulty_level == 1 ? 'Fácil' : ($question->difficulty_level == 2 ? 'Media' : 'Difícil') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.diagnostics.questions.edit', [$diagnostic, $question]) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" 
                                              action="{{ route('admin.diagnostics.questions.destroy', [$diagnostic, $question]) }}" 
                                              class="d-inline"
                                              onsubmit="return confirm('¿Estás seguro de eliminar esta pregunta?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay preguntas agregadas aún</h5>
                    <p class="text-muted">Comienza agregando preguntas a este diagnóstico</p>
                    <a href="{{ route('admin.diagnostics.questions.create', $diagnostic) }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Agregar Primera Pregunta
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection