@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Agregar Nuevo Contenido</h2>
    <form method="POST" action="{{ route('admin.content.store') }}">
        @csrf
        <div class="form-group">
            <label>Título:</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Materia:</label>
            <select name="subject_area" class="form-control" required>
                <option value="Matemáticas">Matemáticas</option>
                <option value="Física">Física</option>
                <option value="Química">Química</option>
                <option value="Programación">Programación</option>
            </select>
        </div>
        <div class="form-group">
            <label>Tipo:</label>
            <select name="type" class="form-control" required>
                <option value="Video">Video</option>
                <option value="Documento">Documento</option>
                <option value="Interactivo">Interactivo</option>
                <option value="Quiz">Quiz</option>
            </select>
        </div>
        <div class="form-group">
            <label>Dificultad:</label>
            <select name="difficulty_level" class="form-control" required>
                <option value="Básico">Básico</option>
                <option value="Intermedio">Intermedio</option>
                <option value="Avanzado">Avanzado</option>
            </select>
        </div>
        <div class="form-group">
            <label>URL del Contenido:</label>
            <input type="url" name="content_url" class="form-control">
        </div>
        <div class="form-group">
            <label>Descripción:</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Crear Contenido</button>
        <a href="{{ route('admin.content.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection