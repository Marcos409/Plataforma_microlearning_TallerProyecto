@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-chart-line me-2"></i>Reportes de Rendimiento</h1>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver a Reportes
                </a>
            </div>

            <div class="row">
                <!-- Rendimiento por Materia -->
                <div class="col-md-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-book me-2"></i>Rendimiento por Materia</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Materia</th>
                                            <th>Promedio</th>
                                            <th>Total Intentos</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($performanceBySubject as $subject)
                                        <tr>
                                            <td><strong>{{ $subject->subject_area }}</strong></td>
                                            <td>
                                                @php
                                                    $avg = round($subject->avg_score);
                                                    $color = $avg >= 70 ? 'success' : ($avg >= 50 ? 'warning' : 'danger');
                                                @endphp
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-{{ $color }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $avg }}%">
                                                        {{ $avg }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $subject->total_attempts }}</span>
                                            </td>
                                            <td>
                                                @if($avg >= 70)
                                                    <span class="badge bg-success">Excelente</span>
                                                @elseif($avg >= 50)
                                                    <span class="badge bg-warning">Regular</span>
                                                @else
                                                    <span class="badge bg-danger">Crítico</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <i class="fas fa-chart-bar fa-2x text-muted mb-2"></i>
                                                <p class="text-muted mb-0">No hay datos de rendimiento disponibles</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top 10 Estudiantes -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top 10 Estudiantes</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @forelse($topStudents as $index => $student)
                                <div class="list-group-item d-flex align-items-center">
                                    <div class="rank-badge me-3">
                                        @if($index < 3)
                                            <span class="badge bg-warning text-dark fs-5">
                                                <i class="fas fa-medal"></i> {{ $index + 1 }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>{{ $student->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $student->diagnostic_responses_count }} diagnósticos
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success fs-6">
                                            {{ round($student->diagnostic_responses_avg_score ?? 0) }}%
                                        </span>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-4">
                                    <i class="fas fa-user-graduate fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No hay datos disponibles</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rendimiento por Período -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Rendimiento Mensual (Últimos 6 Meses)</h5>
                </div>
                <div class="card-body">
                    @if($performanceByMonth->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Período</th>
                                    <th>Promedio</th>
                                    <th>Tendencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($performanceByMonth as $index => $period)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ DateTime::createFromFormat('!m', $period->month)->format('F') }} 
                                            {{ $period->year }}
                                        </strong>
                                    </td>
                                    <td>
                                        @php
                                            $avg = round($period->avg_score);
                                            $color = $avg >= 70 ? 'success' : ($avg >= 50 ? 'warning' : 'danger');
                                        @endphp
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar bg-{{ $color }}" 
                                                 style="width: {{ $avg }}%">
                                                {{ $avg }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($index < $performanceByMonth->count() - 1)
                                            @php
                                                $prevAvg = $performanceByMonth[$index + 1]->avg_score;
                                                $currentAvg = $period->avg_score;
                                                $diff = $currentAvg - $prevAvg;
                                            @endphp
                                            @if($diff > 0)
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i> +{{ round($diff, 1) }}%
                                                </span>
                                            @elseif($diff < 0)
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i> {{ round($diff, 1) }}%
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-minus"></i> Sin cambio
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay datos de rendimiento mensual disponibles</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
}

.rank-badge .badge {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>
@endsection