@extends('layouts.app')

@section('title', 'Preguntas del Diagnóstico')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-question-circle me-2"></i>Preguntas del Diagnóstico</h1>
                    <p class="text-muted mb-0">
                        <strong>Diagnóstico:</strong> {{ $diagnostic->title }} | 
                        <strong>Materia:</strong> {{ $diagnostic->subject_area }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.diagnostics.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver a Diagnósticos
                    </a>
                    <a href="{{ route('admin.diagnostics.questions.create', $diagnostic->id) }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Nueva Pregunta
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

            <!-- Información del diagnóstico -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-primary text-white rounded">
                                <i class="fas fa-list-ol fa-2x mb-2"></i>
                                <h3 class="mb-0">{{ count($questions) }}</h3>
                                <p class="mb-0">Preguntas Totales</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-success text-white rounded">
                                <i class="fas fa-star fa-2x mb-2"></i>
                                <h3 class="mb-0">{{ $diagnostic->passing_score }}%</h3>
                                <p class="mb-0">Puntaje Mínimo</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-info text-white rounded">
                                <i class="fas fa-signal fa-2x mb-2"></i>
                                <h3 class="mb-0">{{ $questions->where('difficulty_level', 1)->count() }}/{{ $questions->where('difficulty_level', 2)->count() }}/{{ $questions->where('difficulty_level', 3)->count() }}</h3>
                                <p class="mb-0">Fácil/Media/Difícil</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-warning text-white rounded">
                                <i class="fas fa-bookmark fa-2x mb-2"></i>
                                <h3 class="mb-0">{{ $questions->unique('topic')->count() }}</h3>
                                <p class="mb-0">Temas Diferentes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de preguntas -->
            @if($questions->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Preguntas</h5>
                    </div>
                    <div class="card-body">
                        @foreach($questions as $index => $question)
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-primary me-2">Pregunta #{{ $index + 1 }}</span>
                                        <span class="badge bg-secondary">Tema: {{ $question->topic ?? 'N/A' }}</span>
                                        @if($question->difficulty_level ?? 'N/A' == 1)
                                            <span class="badge bg-success">Fácil</span>
                                        @elseif($question->difficulty_level  ?? 'N/A' == 2)
                                            <span class="badge bg-warning">Media</span>
                                        @else
                                            <span class="badge bg-danger">Difícil</span>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.diagnostics.questions.edit', [$diagnostic->id, $question->id]) }}" 
                                        
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <form method="POST" 
                                              action="{{ route('admin.diagnostics.questions.destroy', [$diagnostic->id, $question->id]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('¿Estás seguro de eliminar esta pregunta?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h6 class="mb-3"><strong>Pregunta:</strong></h6>
                                    <p class="mb-3">{{ $question->question_text }}</p>
                                    
                                    <h6 class="mb-2"><strong>Opciones:</strong></h6>
                                    <div class="list-group">
                                        @foreach($question->options as $optionIndex => $option)
                                            <div class="list-group-item {{ $question->correct_answer == $optionIndex ? 'list-group-item-success' : '' }}">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-secondary me-2">{{ chr(65 + $optionIndex) }}</span>
                                                    <span>{{ $option }}</span>
                                                    @if($question->correct_answer == $optionIndex)
                                                        <span class="badge bg-success ms-auto">
                                                            <i class="fas fa-check"></i> Correcta
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No hay preguntas agregadas</h4>
                        <p class="text-muted mb-4">Comienza agregando preguntas a este diagnóstico</p>
                        <a href="{{ route('admin.diagnostics.questions.create', $diagnostic->id) }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Agregar Primera Pregunta
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .list-group-item-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    
    .badge {
        font-weight: 500;
    }
</style>
@endsection