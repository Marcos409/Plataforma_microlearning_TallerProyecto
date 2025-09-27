@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Crear Nuevo Estudiante</h2>
    <form method="POST" action="{{ route('admin.students.store') }}">
        @csrf
        <div class="form-group">
            <label>Nombre:</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Código de Estudiante:</label>
            <input type="text" name="student_code" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Carrera:</label>
            <input type="text" name="career" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Semestre:</label>
            <input type="number" name="semester" class="form-control" min="1" max="12" required>
        </div>
        <div class="form-group">
            <label>Teléfono:</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="form-group">
            <label>Contraseña:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Confirmar Contraseña:</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Crear Estudiante</button>
        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection