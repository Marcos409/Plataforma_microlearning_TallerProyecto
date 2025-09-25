@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Mis Rutas de Aprendizaje</h2>
    @if($learningPaths && $learningPaths->count() > 0)
        @foreach($learningPaths as $path)
        <div class="card mb-3">
            <div class="card-body">
                <h5>Ruta de Aprendizaje #{{ $path->id }}</h5>
                <p>Progreso: {{ $path->progress_percentage ?? 0 }}%</p>
                <a href="{{ route('student.learning-paths.show', $path->id) }}" class="btn btn-primary">Ver Detalle</a>
            </div>
        </div>
        @endforeach
    @else
        <div class="alert alert-info">No tienes rutas de aprendizaje asignadas.</div>
    @endif
</div>
@endsection