@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Mis Estudiantes</h2>
    
    @if($students->count() > 0)
        <div class="row">
            @foreach($students as $student)
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5>{{ $student->name }}</h5>
                        <p>{{ $student->email }}</p>
                        <a href="{{ route('teacher.students.show', $student) }}" class="btn btn-primary">
                            Ver Progreso
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">
            No hay estudiantes registrados.
        </div>
    @endif
</div>
@endsection