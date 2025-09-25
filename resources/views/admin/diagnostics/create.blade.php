@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Crear Nuevo Diagnóstico</h2>
    <form method="POST" action="{{ route('admin.diagnostics.store') }}">
        @csrf
        <div class="form-group">
            <label>Título:</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Materia:</label>
            <input type="text" name="subject" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Descripción:</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Crear Diagnóstico</button>
        <a href="{{ route('admin.diagnostics.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection