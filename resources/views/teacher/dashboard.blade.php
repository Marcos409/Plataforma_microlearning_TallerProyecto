@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Panel de Docente</h1>
            <p class="text-muted">
                Bienvenido, {{ Auth::user()->name }}
                @if(Auth::user()->role)
                    - <span class="badge bg-success">{{ Auth::user()->role->name }}</span>
                @endif
            </p>
        </div>
    </div>

@endsection