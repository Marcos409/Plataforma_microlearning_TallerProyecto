@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-users me-2"></i>Reportes de Estudiantes</h1>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver a Reportes
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.students') }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Buscar Estudiante</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       placeholder="Nombre o email..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Estado</label>
                                <select class="form-select" name="status">
                                    <option value="">Todos</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Estudiantes -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Lista de Estudiantes ({{ $students->total() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Estudiante</th>
                                    <th>Email</th>
                                    <th>Diagnósticos</th>
                                    <th>Actividades</th>
                                    <th>Promedio</th>
                                    <th>Último Acceso</th>
                                    <th>Riesgo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr>
                                    <td>{{ $student->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white me-2">
                                                {{ substr($student->name, 0, 1) }}
                                            </div>
                                            <strong>{{ $student->name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ $student->email }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $student->total_diagnostics }} completados
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ $student->completed_activities }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $score = round($student->average_score);
                                            $color = $score >= 70 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
                                        @endphp
                                        <span class="badge bg-{{ $color }}">
                                            {{ $score }}%
                                        </span>
                                    </td>
                                    <td>
                                        @if(isset($student->last_login_at) && $student->last_login_at)
                                            <small>{{ \Carbon\Carbon::parse($student->last_login_at)->diffForHumans() }}</small>
                                        @else
                                            <small class="text-muted">Nunca</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $riskBadges = [
                                                0 => ['text' => 'Bajo', 'color' => 'success'],
                                                1 => ['text' => 'Medio', 'color' => 'warning'],
                                                2 => ['text' => 'Alto', 'color' => 'danger'],
                                                3 => ['text' => 'Crítico', 'color' => 'dark']
                                            ];
                                            $risk = $riskBadges[$student->risk_level] ?? $riskBadges[0];
                                        @endphp
                                        <span class="badge bg-{{ $risk['color'] }}">
                                            {{ $risk['text'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.students.show', $student->id) }}" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No se encontraron estudiantes</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-3">
                        {{ $students->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}

.card {
    border: none;
}
</style>
@endsection