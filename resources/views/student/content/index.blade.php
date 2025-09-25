@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Biblioteca de Contenidos</h4>
                </div>

                <div class="card-body">
                    <!-- Filtros de búsqueda -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="GET" class="form-inline">
                                <div class="form-group mx-2">
                                    <label for="search">Buscar:</label>
                                    <input type="text" name="search" id="search" class="form-control ml-2" 
                                           value="{{ request('search') }}" placeholder="Título o descripción...">
                                </div>
                                
                                <div class="form-group mx-2">
                                    <label for="subject">Materia:</label>
                                    <select name="subject" id="subject" class="form-control ml-2">
                                        <option value="">Todas las materias</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject }}" {{ request('subject') == $subject ? 'selected' : '' }}>
                                                {{ $subject }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group mx-2">
                                    <label for="type">Tipo:</label>
                                    <select name="type" id="type" class="form-control ml-2">
                                        <option value="">Todos los tipos</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group mx-2">
                                    <label for="difficulty">Dificultad:</label>
                                    <select name="difficulty" id="difficulty" class="form-control ml-2">
                                        <option value="">Todas</option>
                                        <option value="Básico" {{ request('difficulty') == 'Básico' ? 'selected' : '' }}>Básico</option>
                                        <option value="Intermedio" {{ request('difficulty') == 'Intermedio' ? 'selected' : '' }}>Intermedio</option>
                                        <option value="Avanzado" {{ request('difficulty') == 'Avanzado' ? 'selected' : '' }}>Avanzado</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary mx-2">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Contenidos recomendados -->
                    @if($recommended && $recommended->count() > 0)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="text-primary">Contenidos Recomendados</h5>
                            <div class="row">
                                @foreach($recommended as $rec)
                                <div class="col-md-4 mb-3">
                                    <div class="card border-primary">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="card-title">{{ $rec->content->title ?? 'Contenido' }}</h6>
                                                <span class="badge badge-primary">Recomendado</span>
                                            </div>
                                            <p class="card-text text-truncate">
                                                {{ $rec->content->description ?? 'Sin descripción' }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <span class="badge badge-info">{{ $rec->content->subject_area ?? 'N/A' }}</span>
                                                </small>
                                                @if($rec->content)
                                                    <a href="{{ route('student.content.show', $rec->content->id) }}" 
                                                       class="btn btn-sm btn-primary">Ver</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Lista de contenidos -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5>Todos los Contenidos</h5>
                        </div>
                    </div>

                    @if($contents && $contents->count() > 0)
                    <div class="row">
                        @foreach($contents as $content)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $content->title }}</h6>
                                    <p class="card-text">
                                        {{ Str::limit($content->description ?? 'Sin descripción', 100) }}
                                    </p>
                                    <div class="mb-2">
                                        <span class="badge badge-info">{{ $content->subject_area }}</span>
                                        <span class="badge badge-secondary">{{ $content->type }}</span>
                                        <span class="badge badge-warning">{{ $content->difficulty_level }}</span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">{{ $content->topic ?? 'Sin tema' }}</small>
                                        <a href="{{ route('student.content.show', $content->id) }}" 
                                           class="btn btn-sm btn-primary">Ver Contenido</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Paginación -->
                    @if(method_exists($contents, 'links'))
                    <div class="row mt-4">
                        <div class="col-md-12">
                            {{ $contents->links() }}
                        </div>
                    </div>
                    @endif

                    @else
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h5>No hay contenidos disponibles</h5>
                                <p>No se encontraron contenidos que coincidan con los filtros seleccionados.</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection