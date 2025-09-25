@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $diagnostic->title }}</h4>
                        <div id="timer" class="badge bg-light text-dark fs-6">
                            <i class="fas fa-clock"></i> <span id="time-display">00:00</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <p class="text-muted">{{ $diagnostic->description }}</p>
                        </div>
                        <div class="col-md-4">
                            <div class="progress mb-2">
                                <div class="progress-bar" id="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted">
                                Pregunta <span id="current-question">1</span> de {{ $diagnostic->questions->count() }}
                            </small>
                        </div>
                    </div>

                    <form id="diagnostic-form" action="{{ route('student.diagnostics.submit', $diagnostic) }}" method="POST">
                        @csrf
                        <div id="questions-container">
                            @foreach($diagnostic->questions as $index => $question)
                                <div class="question-slide" data-question="{{ $index + 1 }}" style="display: {{ $index === 0 ? 'block' : 'none' }}">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title">Pregunta {{ $index + 1 }}</h5>
                                            <h6 class="mb-3">{{ $question->question }}</h6>
                                            
                                            <div class="options">
                                                @foreach($question->options as $optionIndex => $option)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="radio" 
                                                               name="responses[{{ $question->id }}]" 
                                                               value="{{ $optionIndex }}" 
                                                               id="q{{ $question->id }}_{{ $optionIndex }}"
                                                               onchange="saveResponse({{ $question->id }}, {{ $optionIndex }})">
                                                        <label class="form-check-label" for="q{{ $question->id }}_{{ $optionIndex }}">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <input type="hidden" name="time_spent[{{ $question->id }}]" id="time_{{ $question->id }}" value="0">
                                            
                                            <div class="d-flex justify-content-between mt-4">
                                                <button type="button" class="btn btn-outline-secondary" id="prev-btn" 
                                                        onclick="previousQuestion()" 
                                                        {{ $index === 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-chevron-left"></i> Anterior
                                                </button>
                                                
                                                @if($index === $diagnostic->questions->count() - 1)
                                                    <button type="button" class="btn btn-success" onclick="submitDiagnostic()">
                                                        <i class="fas fa-check"></i> Finalizar Diagnóstico
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-primary" id="next-btn" onclick="nextQuestion()">
                                                        Siguiente <i class="fas fa-chevron-right"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas finalizar el diagnóstico?</p>
                <p class="text-muted">Una vez enviado, no podrás modificar tus respuestas.</p>
                <div id="unanswered-warning" class="alert alert-warning" style="display: none;">
                    <strong>Atención:</strong> Hay preguntas sin responder. Se marcarán como incorrectas.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmSubmit()">Enviar Diagnóstico</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentQuestion = 1;
let totalQuestions = {{ $diagnostic->questions->count() }};
let responses = {};
let questionTimes = {};
let startTime = Date.now();
let questionStartTime = Date.now();

// Timer
let totalSeconds = 0;
setInterval(function() {
    totalSeconds++;
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    document.getElementById('time-display').textContent = 
        String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
}, 1000);

function updateProgress() {
    const progress = (currentQuestion / totalQuestions) * 100;
    document.getElementById('progress-bar').style.width = progress + '%';
    document.getElementById('current-question').textContent = currentQuestion;
}

function saveResponse(questionId, answer) {
    responses[questionId] = answer;
    
    // Guardar tiempo gastado en esta pregunta
    const currentTime = Date.now();
    const timeSpent = Math.round((currentTime - questionStartTime) / 1000);
    questionTimes[questionId] = (questionTimes[questionId] || 0) + timeSpent;
    document.getElementById('time_' + questionId).value = questionTimes[questionId];
}

function nextQuestion() {
    if (currentQuestion < totalQuestions) {
        // Ocultar pregunta actual
        document.querySelector(`[data-question="${currentQuestion}"]`).style.display = 'none';
        
        currentQuestion++;
        questionStartTime = Date.now();
        
        // Mostrar siguiente pregunta
        document.querySelector(`[data-question="${currentQuestion}"]`).style.display = 'block';
        
        updateProgress();
        updateButtons();
    }
}

function previousQuestion() {
    if (currentQuestion > 1) {
        // Ocultar pregunta actual
        document.querySelector(`[data-question="${currentQuestion}"]`).style.display = 'none';
        
        currentQuestion--;
        questionStartTime = Date.now();
        
        // Mostrar pregunta anterior
        document.querySelector(`[data-question="${currentQuestion}"]`).style.display = 'block';
        
        updateProgress();
        updateButtons();
    }
}

function updateButtons() {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    
    if (prevBtn) {
        prevBtn.disabled = currentQuestion === 1;
    }
}

function submitDiagnostic() {
    // Verificar preguntas sin responder
    const totalQuestions = {{ $diagnostic->questions->count() }};
    const answeredQuestions = Object.keys(responses).length;
    
    if (answeredQuestions < totalQuestions) {
        document.getElementById('unanswered-warning').style.display = 'block';
    } else {
        document.getElementById('unanswered-warning').style.display = 'none';
    }
    
    // Mostrar modal de confirmación
    const modal = new bootstrap.Modal(document.getElementById('submitModal'));
    modal.show();
}

function confirmSubmit() {
    document.getElementById('diagnostic-form').submit();
}

// Prevenir navegación accidental
window.addEventListener('beforeunload', function (e) {
    if (Object.keys(responses).length > 0) {
        e.preventDefault();
        e.returnValue = '';
        return 'Tienes respuestas sin guardar. ¿Estás seguro de que quieres salir?';
    }
});

// Inicializar
updateProgress();
</script>
@endsection