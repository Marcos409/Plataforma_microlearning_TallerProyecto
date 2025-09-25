@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Detalle de Ruta de Aprendizaje</h2>
    <div class="card">
        <div class="card-body">
            <h5>Ruta #{{ $learningPath->id }}</h5>
            <p>Progreso: {{ $learningPath->progress_percentage ?? 0 }}%</p>
            <!-- Aquí mostrarías los contenidos de la ruta -->
        </div>
    </div>
</div>
@endsection