@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Mis Recomendaciones</h2>
    @if($recommendations && $recommendations->count() > 0)
        @foreach($recommendations as $rec)
        <div class="card mb-3">
            <div class="card-body">
                <h5>{{ $rec->content->title ?? 'Contenido Recomendado' }}</h5>
                <p>{{ $rec->content->description ?? 'Sin descripción' }}</p>
                @if($rec->content)
                    <a href="{{ route('student.content.show', $rec->content->id) }}" class="btn btn-primary">Ver Contenido</a>
                @endif
            </div>
        </div>
        @endforeach
    @else
        <div class="alert alert-info">No tienes recomendaciones pendientes.</div>
    @endif
</div>
@endsection