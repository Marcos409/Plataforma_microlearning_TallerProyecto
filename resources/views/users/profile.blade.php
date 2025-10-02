@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Perfil de Usuario</div>
                
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>Nombre:</strong></label>
                        <p>{{ Auth::user()->name }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Email:</strong></label>
                        <p>{{ Auth::user()->email }}</p>
                    </div>
                    
                    @if(Auth::user()->role)
                    <div class="mb-3">
                        <label class="form-label"><strong>Rol:</strong></label>
                        <p>
                            <span class="badge bg-primary">{{ Auth::user()->role->name }}</span>
                        </p>
                        @if(Auth::user()->role->description)
                            <small class="text-muted">{{ Auth::user()->role->description }}</small>
                        @endif
                    </div>
                    @endif
                    
                    @if(Auth::user()->isStudent())
                        @if(Auth::user()->student_code)
                        <div class="mb-3">
                            <label class="form-label"><strong>Código de Estudiante:</strong></label>
                            <p>{{ Auth::user()->student_code }}</p>
                        </div>
                        @endif
                        
                        @if(Auth::user()->career)
                        <div class="mb-3">
                            <label class="form-label"><strong>Carrera:</strong></label>
                            <p>{{ Auth::user()->career }}</p>
                        </div>
                        @endif
                        
                        @if(Auth::user()->semester)
                        <div class="mb-3">
                            <label class="form-label"><strong>Semestre:</strong></label>
                            <p>{{ Auth::user()->semester }}</p>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection