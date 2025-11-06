@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-users me-2"></i>Gestión de Estudiantes</h4>
                    <div>
                        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Estudiante
                        </a>
                        <a href="{{ route('admin.users.export', ['students_only' => true]) }}" class="btn btn-info">
                            <i class="fas fa-download me-1"></i>Exportar CSV
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <!-- Filtros de búsqueda -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Buscar por nombre, email o código..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="career" class="form-control">
                                <option value="">Todas las carreras</option>
                                @foreach($careers as $career)
                                    <option value="{{ $career }}" {{ request('career') == $career ? 'selected' : '' }}>
                                        {{ $career }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="semester" class="form-control">
                                <option value="">Todos los semestres</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>
                                        {{ $i }}° Semestre
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-secondary" onclick="filterStudents()">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>

                    <!-- Estadísticas rápidas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center bg-primary text-white">
                                <div class="card-body">
                                    <h4>{{ $students->total() ?? 0 }}</h4>
                                    <p class="mb-0">Total Estudiantes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center bg-success text-white">
                                <div class="card-body">
                                    <h4>{{ $students->where('active', true)->count() ?? 0 }}</h4>
                                    <p class="mb-0">Activos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center bg-info text-white">
                                <div class="card-body">
                                    <h4>{{ $careers->count() ?? 0 }}</h4>
                                    <p class="mb-0">Carreras</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center bg-warning text-white">
                                <div class="card-body">
                                    <h4>{{ $students->where('created_at', '>=', now()->subMonth())->count() ?? 0 }}</h4>
                                    <p class="mb-0">Nuevos este mes</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de estudiantes -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Carrera</th>
                                    <th>Semestre</th>
                                    <th>Estado</th>
                                    <th>Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr>
                                    <td><strong>{{ $student->student_code ?? 'N/A' }}</strong></td>
                                    <td>{{ $student['name'] ?? 'N/A' }}</td>
                                    <td>{{ $student['email'] ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $student->career ?? 'Sin carrera' }}</span>
                                    </td>
                                    <td>{{ $student->semester ?? 'N/A' }}°</td>
                                    <td>
                                        @if($student->active ?? true)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($student['created_at'])->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.students.show', $student['id']) }}" 
                                               class="btn btn-sm btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.students.edit', $student['id']) }}" 
                                               class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.students.destroy', $student['id']) }}" 
                                                  style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        title="Eliminar"
                                                        onclick="return confirm('¿Estás seguro de eliminar este estudiante?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="alert alert-info">
                                            No se encontraron estudiantes con los filtros seleccionados.
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if(method_exists($students, 'links'))
                    <div class="row mt-4">
                        <div class="col-md-12">
                            {{ $students->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterStudents() {
    const search = document.querySelector('input[name="search"]').value;
    const career = document.querySelector('select[name="career"]').value;
    const semester = document.querySelector('select[name="semester"]').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (career) params.append('career', career);
    if (semester) params.append('semester', semester);
    
    window.location.href = '{{ route("admin.students.index") }}?' + params.toString();
}

function clearFilters() {
    window.location.href = '{{ route("admin.students.index") }}';
}

// Permitir filtrar al presionar Enter
document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        filterStudents();
    }
});
</script>
@endsection