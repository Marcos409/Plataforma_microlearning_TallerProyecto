@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Dashboard del Docente</h1>
    <p>Bienvenido al panel de docentes</p>
    <p>Usuario: {{ Auth::user()->name }}</p>
    <p>Rol: {{ Auth::user()->role->name ?? 'No asignado' }}</p>
</div>
@endsection