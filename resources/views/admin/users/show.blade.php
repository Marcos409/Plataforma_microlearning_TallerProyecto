@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Perfil de Usuario: {{ $user->name }}</h4>
                    <div>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Información Personal -->
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">Información Personal</h5>
                            
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Teléfono:</strong></td>
                                    <td>{{ $user->phone ?? 'No registrado' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $user->active ? 'success' : 'secondary' }} badge-pill">
                                            {{ $user->active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Email Verificado:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $user->email_verified_at ? 'success' : 'warning' }} badge-pill">
                                            {{ $user->email_verified_at ? 'Verificado' : 'Pendiente' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Información del Sistema -->
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">Información del Sistema</h5>
                            
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Rol:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $user->role && $user->role->name === 'Administrador' ? 'danger' : ($user->role && $user->role->name === 'Docente' ? 'success' : 'primary') }} badge-pill">
                                            {{ $user->role ? $user->role->name : 'Sin asignar' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>ID de Usuario:</strong></td>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Registro:</strong></td>
                                    <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Última Actualización:</strong></td>
                                    <td>{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Información Académica (solo para estudiantes) -->
                    @if($user->role && $user->role->name === 'Estudiante')
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="text-primary mb-3">Información Académica</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Código de Estudiante:</strong></td>
                                            <td>{{ $user->student_code ?? 'No asignado' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Carrera:</strong></td>
                                            <td>{{ $user->career ?? 'No registrada' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Semestre:</strong></td>
                                            <td>{{ $user->semester ? $user->semester . '° Semestre' : 'No registrado' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <!-- Estadísticas de progreso si están disponibles -->
                                    @if($user->studentProgress && $user->studentProgress->count() > 0)
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Resumen de Progreso</h6>
                                            <p class="mb-1">Materias activas: {{ $user->studentProgress->count() }}</p>
                                            <p class="mb-1">Promedio general: {{ number_format($user->studentProgress->avg('progress_percentage'), 1) }}%</p>
                                            <p class="mb-0">Tiempo total: {{ floor($user->studentProgress->sum('total_time_spent') / 60) }} horas</p>
                                        </div>
                                    </div>
                                    @else
                                    <div class="alert alert-info">
                                        <small>No hay datos de progreso académico disponibles.</small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Progreso Académico (solo para estudiantes) -->
                    @if($user->role && $user->role->name === 'Estudiante' && $user->studentProgress && $user->studentProgress->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="text-primary mb-3">Progreso por Materia</h5>
                            
                            @foreach($user->studentProgress as $progress)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">{{ $progress->subject_area }}</h6>
                                        <span class="badge badge-info">{{ number_format($progress->progress_percentage, 1) }}%</span>
                                    </div>
                                    <div class="progress mb-2">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $progress->progress_percentage }}%">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted">Actividades: {{ $progress->completed_activities }}/{{ $progress->total_activities }}</small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Promedio: {{ number_format($progress->average_score, 1) }}%</small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Tiempo: {{ floor($progress->total_time_spent / 60) }}h</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Rutas de Aprendizaje (solo para estudiantes) -->
                    @if($user->role && $user->role->name === 'Estudiante' && $user->learningPaths && $user->learningPaths->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="text-primary mb-3">Rutas de Aprendizaje</h5>
                            
                            <div class="row">
                                @foreach($user->learningPaths as $path)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>{{ $path->name ?? 'Ruta #' . $path->id }}</h6>
                                            <p class="text-muted mb-2">{{ $path->subject_area }}</p>
                                            <div class="progress mb-2">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $path->progress_percentage ?? 0 }}%">
                                                    {{ number_format($path->progress_percentage ?? 0, 1) }}%
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                {{ $path->is_completed ? 'Completada' : 'En progreso' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Acciones Administrativas -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="text-primary mb-3">Acciones Administrativas</h5>
                            
                            <div class="d-flex justify-content-start">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary me-2">
                                    <i class="fas fa-edit"></i> Editar Usuario
                                </a>
                                
                                @if($user->role && $user->role->name === 'Estudiante')
                                <a href="{{ route('admin.students.show', $user->id) }}" class="btn btn-info me-2">
                                    <i class="fas fa-graduation-cap"></i> Ver como Estudiante
                                </a>
                                @endif
                                
                                @if($user->id !== 1 && $user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" 
                                      style="display: inline;" 
                                      onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Eliminar Usuario
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection