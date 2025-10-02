@extends('layouts.app')

@section('title', 'Crear Nueva Pregunta')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-plus-circle me-2"></i>Nueva Pregunta</h1>
                    <p class="text-muted mb-0">
                        <strong>Diagnóstico:</strong> {{ $diagnostic->title }} | 
                        <strong>Materia:</strong> {{ $diagnostic->subject_area }}
                    </p>
                </div>
                <a href="{{ route('admin.diagnostics.questions.index', $diagnostic) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver a Preguntas
                </a>
            </div>

            <!-- Formulario -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Datos de la Pregunta</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.diagnostics.questions.store', $diagnostic) }}" 
                          method="POST" 
                          id="questionForm">
                        @csrf

                        <!-- Pregunta -->
                        <div class="mb-4">
                            <label for="question" class="form-label fw-bold">
                                <i class="fas fa-question me-1"></i>Pregunta <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('question') is-invalid @enderror" 
                                      id="question" 
                                      name="question" 
                                      rows="3" 
                                      placeholder="Escribe la pregunta aquí..."
                                      required>{{ old('question') }}</textarea>
                            @error('question')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Escribe una pregunta clara y precisa</small>
                        </div>

                        <div class="row">
                            <!-- Tema -->
                            <div class="col-md-6 mb-4">
                                <label for="topic" class="form-label fw-bold">
                                    <i class="fas fa-bookmark me-1"></i>Tema <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('topic') is-invalid @enderror" 
                                       id="topic" 
                                       name="topic" 
                                       placeholder="Ej: Álgebra, Ecuaciones, Trigonometría..."
                                       value="{{ old('topic') }}"
                                       required>
                                @error('topic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nivel de Dificultad -->
                            <div class="col-md-6 mb-4">
                                <label for="difficulty_level" class="form-label fw-bold">
                                    <i class="fas fa-signal me-1"></i>Nivel de Dificultad <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('difficulty_level') is-invalid @enderror" 
                                        id="difficulty_level" 
                                        name="difficulty_level" 
                                        required>
                                    <option value="">Seleccionar nivel...</option>
                                    <option value="1" {{ old('difficulty_level') == 1 ? 'selected' : '' }}>
                                        🟢 Fácil
                                    </option>
                                    <option value="2" {{ old('difficulty_level') == 2 ? 'selected' : '' }}>
                                        🟡 Medio
                                    </option>
                                    <option value="3" {{ old('difficulty_level') == 3 ? 'selected' : '' }}>
                                        🔴 Difícil
                                    </option>
                                </select>
                                @error('difficulty_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Opciones de Respuesta -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-list-ul me-1"></i>Opciones de Respuesta <span class="text-danger">*</span>
                            </label>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-info-circle"></i> Agrega entre 2 y 5 opciones. Marca con el radio button la respuesta correcta.
                            </p>
                            
                            <div id="optionsContainer">
                                @for($i = 0; $i < 4; $i++)
                                <div class="mb-3 option-row">
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white fw-bold" style="width: 50px;">
                                            {{ chr(65 + $i) }}
                                        </span>
                                        <input type="text" 
                                               class="form-control @error('options.'.$i) is-invalid @enderror" 
                                               name="options[]" 
                                               placeholder="Escribe la opción {{ chr(65 + $i) }}"
                                               value="{{ old('options.'.$i) }}"
                                               {{ $i < 2 ? 'required' : '' }}>
                                        <div class="input-group-text" style="min-width: 130px;">
                                            <input class="form-check-input mt-0 me-2" 
                                                   type="radio" 
                                                   name="correct_answer" 
                                                   value="{{ $i }}"
                                                   {{ old('correct_answer') == $i ? 'checked' : '' }}
                                                   required>
                                            <label class="form-check-label">
                                                <i class="fas fa-check-circle text-success"></i> Correcta
                                            </label>
                                        </div>
                                        @if($i >= 2)
                                        <button type="button" 
                                                class="btn btn-outline-danger"
                                                onclick="removeOption(this)"
                                                title="Eliminar opción">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                    </div>
                                    @error('options.'.$i)
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endfor
                            </div>

                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary" 
                                    id="addOptionBtn"
                                    onclick="addOption()">
                                <i class="fas fa-plus me-1"></i>Agregar Opción (máx. 5)
                            </button>

                            @error('correct_answer')
                                <div class="text-danger small mt-2">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="{{ route('admin.diagnostics.questions.index', $diagnostic) }}" 
                               class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Guardar Pregunta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let optionCount = 4;

function addOption() {
    if (optionCount >= 5) {
        alert('⚠️ Máximo 5 opciones permitidas');
        return;
    }
    
    const container = document.getElementById('optionsContainer');
    const letter = String.fromCharCode(65 + optionCount);
    
    const newOption = `
        <div class="mb-3 option-row">
            <div class="input-group">
                <span class="input-group-text bg-primary text-white fw-bold" style="width: 50px;">
                    ${letter}
                </span>
                <input type="text" 
                       class="form-control" 
                       name="options[]" 
                       placeholder="Escribe la opción ${letter}">
                <div class="input-group-text" style="min-width: 130px;">
                    <input class="form-check-input mt-0 me-2" 
                           type="radio" 
                           name="correct_answer" 
                           value="${optionCount}" 
                           required>
                    <label class="form-check-label">
                        <i class="fas fa-check-circle text-success"></i> Correcta
                    </label>
                </div>
                <button type="button" 
                        class="btn btn-outline-danger"
                        onclick="removeOption(this)"
                        title="Eliminar opción">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', newOption);
    optionCount++;
    
    if (optionCount >= 5) {
        document.getElementById('addOptionBtn').disabled = true;
        document.getElementById('addOptionBtn').innerHTML = '<i class="fas fa-check me-1"></i>Límite alcanzado';
    }
}

function removeOption(button) {
    if (document.querySelectorAll('.option-row').length <= 2) {
        alert('⚠️ Debe haber al menos 2 opciones');
        return;
    }
    
    const row = button.closest('.option-row');
    row.remove();
    optionCount--;
    
    // Reactivar botón de agregar
    const addBtn = document.getElementById('addOptionBtn');
    addBtn.disabled = false;
    addBtn.innerHTML = '<i class="fas fa-plus me-1"></i>Agregar Opción (máx. 5)';
    
    // Reindexar opciones
    const options = document.querySelectorAll('#optionsContainer .option-row');
    options.forEach((opt, index) => {
        const letter = String.fromCharCode(65 + index);
        opt.querySelector('.input-group-text').textContent = letter;
        opt.querySelector('input[type="radio"]').value = index;
        opt.querySelector('input[type="text"]').placeholder = `Escribe la opción ${letter}`;
    });
}

// Validación antes de enviar
document.getElementById('questionForm').addEventListener('submit', function(e) {
    const options = document.querySelectorAll('input[name="options[]"]');
    const filledOptions = Array.from(options).filter(opt => opt.value.trim() !== '');
    
    if (filledOptions.length < 2) {
        e.preventDefault();
        alert('⚠️ Debes agregar al menos 2 opciones de respuesta');
        return false;
    }
    
    const correctAnswer = document.querySelector('input[name="correct_answer"]:checked');
    if (!correctAnswer) {
        e.preventDefault();
        alert('⚠️ Debes seleccionar una respuesta correcta');
        return false;
    }
});
</script>

<style>
.card {
    border: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.option-row {
    transition: all 0.3s ease;
}

.option-row:hover {
    background-color: #f8f9fa;
    padding: 5px;
    border-radius: 5px;
}

.input-group-text {
    border-color: #dee2e6;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>
@endsection