@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Resultados Generales -->
            <div class="card mb-4">
                <div class="card-header bg-{{ $score >= $diagnostic->passing_score ? 'success' : 'warning' }} text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-0">
                                <i class="fas fa-{{ $score >= $diagnostic->passing_score ? 'check-circle' : 'exclamation-triangle' }}"></i>
                                Resultado: {{ $diagnostic->title }}
                            </h4>
                        </div>
                        <div class="col-md-4 text-end">
                            <h3 class="mb-0">{{ number_format($score, 1) }}%</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="progress-circle me-3" style="background: conic-gradient(#28a745 {{ $score * 3.6 }}deg, #e9ecef 0deg);">
                                    <span class="fw-bold">{{ number_format($score) }}%</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Puntaje Obtenido</h6>
                                    <p class="text-muted mb-0">{{ $correctAnswers }}/{{ $totalQuestions }} respuestas correctas</p>
                                </div>
                            </div>
                            
                            <div class="alert alert-{{ $score >= $diagnostic->passing_score ? 'success' : 'warning' }}">
                                @if($score >= $diagnostic->passing_score)
                                    <strong>¡Excelente!</strong> Has aprobado el diagnóstico.
                                @else
                                    <strong>Necesitas refuerzo.</strong> Puntaje mínimo requerido: {{ $diagnostic->passing_score }}%
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Análisis por Temas</h6>
                            @foreach($topicAnalysis as $topic => $analysis)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="small">{{ $topic }}</span>
                                        <span class="small">{{ number_format($analysis['percentage'], 1) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $analysis['percentage'] >= 60 ? 'success' : 'warning' }}" 
                                             style="width: {{ $analysis['percentage'] }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $analysis['correct'] }}/{{ $analysis['total'] }} correctas</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recomendaciones -->
            @if($score < $diagnostic->passing_score)
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Recomendaciones Personalizadas</h5>
                    </div>
                    <div class="card-body">
                        <p>Basándose en tus resultados, te recomendamos revisar los siguientes temas:</p>
                        
                        @foreach($topicAnalysis as $topic => $analysis)
                            @if($analysis['percentage'] < 60)
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> {{ $topic }}</h6>
                                    <p class="mb-2">Obtuviste {{ number_format($analysis['percentage'], 1) }}% en este tema.</p>
                                    <a href="{{ route('student.content.index', ['topic' => $topic, 'subject' => $diagnostic->subject_area]) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-book"></i> Ver Contenidos de Refuerzo
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Detalle de Respuestas -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Detalle de Respuestas</h5>
                </div>
                <div class="card-body">
                    @foreach($responses as $index => $response)
                        <div class="card mb-3 border-{{ $response->is_correct ? 'success' : 'danger' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-{{ $response->is_correct ? 'success' : 'danger' }}">
                                        Pregunta {{ $index + 1 }}
                                    </span>
                                    <small class="text-muted">
                                        <i class="fas fa-tag"></i> {{ $response->question->topic }}
                                    </small>
                                </div>
                                
                                <h6 class="mb-3">{{ $response->question->question }}</h6>
                                
                                <div class="row">
                                    @foreach($response->question->options as $optionIndex => $option)
                                        @php
                                            $isSelected = $response->selected_answer == $optionIndex;
                                            $isCorrect = $response->question->correct_answer == $optionIndex;
                                            $classes = '';
                                            $icon = '';
                                            
                                            if ($isCorrect) {
                                                $classes = 'bg-success text-white';
                                                $icon = 'fas fa-check';
                                            } elseif ($isSelected && !$isCorrect) {
                                                $classes = 'bg-danger text-white';
                                                $icon = 'fas fa-times';
                                            } elseif ($isSelected) {
                                                $classes = 'bg-primary text-white';
                                                $icon = 'fas fa-check';
                                            }
                                        @endphp
                                        
                                        <div class="col-md-6 mb-2">
                                            <div class="p-2 border rounded {{ $classes }}">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        @if($icon)
                                                            <i class="{{ $icon }}"></i>
                                                        @else
                                                            <span class="text-muted">{{ chr(65 + $optionIndex) }}.</span>
                                                        @endif
                                                    </div>
                                                    <span>{{ $option }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if(!$response->is_correct)
                                    <div class="alert alert-info mt-3">
                                        <small>
                                            <i class="fas fa-info-circle"></i>
                                            La respuesta correcta es: <strong>{{ $response->question->options[$response->question->correct_answer] }}</strong>
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Acciones -->
            <div class="card mt-4">
                <div class="card-body text-center">
                    <a href="{{ route('student.diagnostics.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left"></i> Volver a Diagnósticos
                    </a>
                    <a href="{{ route('student.dashboard') }}" class="btn btn-primary me-2">
                        <i class="fas fa-tachometer-alt"></i> Ir al Dashboard
                    </a>
                    @if($score < $diagnostic->passing_score)
                        <a href="{{ route('student.content.index', ['subject' => $diagnostic->subject_area]) }}" class="btn btn-success">
                            <i class="fas fa-book"></i> Ver Contenidos de Refuerzo
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<?php