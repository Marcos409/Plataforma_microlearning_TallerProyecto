{{-- resources/views/admin/reports/group-pdf.blade.php --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Grupo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
        }
        .info-section {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stats-row {
            display: table-row;
        }
        .stats-cell {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        .stats-cell h3 {
            margin: 0;
            font-size: 24px;
            color: #007bff;
        }
        .stats-cell p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .section-title {
            background-color: #007bff;
            color: white;
            padding: 8px 12px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-radius: 3px;
            font-weight: bold;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-primary { background-color: #007bff; color: white; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    {{-- ENCABEZADO --}}
    <div class="header">
        <h1>📊 INFORME DE GRUPO</h1>
        <p>Sistema de Tutoría Virtual Inteligente</p>
    </div>

    {{-- INFORMACIÓN DEL REPORTE --}}
    <div class="info-section">
        <div class="info-row">
            <strong>Fecha de generación:</strong>
            <span>{{ date('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <strong>Carrera:</strong>
            <span>{{ $carrera ?? 'Todas las carreras' }}</span>
        </div>
        <div class="info-row">
            <strong>Semestre:</strong>
            <span>{{ $semestre ?? 'Todos los semestres' }}</span>
        </div>
    </div>

    {{-- ESTADÍSTICAS GENERALES --}}
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stats-cell">
                <h3>{{ $groupStats['total_estudiantes'] }}</h3>
                <p>Total Estudiantes</p>
            </div>
            <div class="stats-cell">
                <h3>{{ $groupStats['promedio_grupo'] }}%</h3>
                <p>Promedio Grupo</p>
            </div>
            <div class="stats-cell">
                <h3>{{ $groupStats['activos_ultimo_mes'] }}</h3>
                <p>Activos (30 días)</p>
            </div>
            <div class="stats-cell">
                <h3>{{ $groupStats['en_riesgo'] }}</h3>
                <p>En Riesgo</p>
            </div>
        </div>
    </div>

    {{-- DISTRIBUCIÓN DE RENDIMIENTO --}}
    <div class="section-title">📈 DISTRIBUCIÓN DE RENDIMIENTO</div>
    <table>
        <thead>
            <tr>
                <th>Categoría</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <span class="badge badge-success">Excelente</span> (≥90%)
                </td>
                <td>{{ $groupStats['excelente'] }}</td>
                <td>{{ $groupStats['total_estudiantes'] > 0 ? round(($groupStats['excelente'] / $groupStats['total_estudiantes']) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td>
                    <span class="badge badge-primary">Aprobado</span> (60-89%)
                </td>
                <td>{{ $groupStats['aprobados'] }}</td>
                <td>{{ $groupStats['total_estudiantes'] > 0 ? round(($groupStats['aprobados'] / $groupStats['total_estudiantes']) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td>
                    <span class="badge badge-warning">Reprobado</span> (50-59%)
                </td>
                <td>{{ $groupStats['reprobados'] }}</td>
                <td>{{ $groupStats['total_estudiantes'] > 0 ? round(($groupStats['reprobados'] / $groupStats['total_estudiantes']) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td>
                    <span class="badge badge-danger">En Riesgo</span> (<50%)
                </td>
                <td>{{ $groupStats['en_riesgo'] }}</td>
                <td>{{ $groupStats['total_estudiantes'] > 0 ? round(($groupStats['en_riesgo'] / $groupStats['total_estudiantes']) * 100, 1) : 0 }}%</td>
            </tr>
        </tbody>
    </table>

    {{-- ÁREAS DÉBILES --}}
    @if(count($groupStats['areas_debiles']) > 0)
    <div class="section-title">⚠️ ÁREAS DÉBILES DEL GRUPO</div>
    <table>
        <thead>
            <tr>
                <th>Materia</th>
                <th>Promedio</th>
                <th>Estudiantes Afectados</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupStats['areas_debiles'] as $area)
            <tr>
                <td><strong>{{ $area['materia'] }}</strong></td>
                <td>
                    <span class="badge badge-{{ $area['promedio'] < 50 ? 'danger' : 'warning' }}">
                        {{ $area['promedio'] }}%
                    </span>
                </td>
                <td>{{ $area['estudiantes_afectados'] }}</td>
                <td>
                    @if($area['promedio'] < 50)
                        🔴 Crítico
                    @else
                        ⚠️ Atención
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- MEJORES ESTUDIANTES --}}
    @if(count($groupStats['mejores_estudiantes']) > 0)
    <div class="section-title">🏆 TOP 5 MEJORES ESTUDIANTES</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Puntuación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupStats['mejores_estudiantes'] as $index => $student)
            <tr>
                <td><strong>#{{ $index + 1 }}</strong></td>
                <td>{{ $student['nombre'] }}</td>
                <td>{{ $student['email'] }}</td>
                <td>
                    <span class="badge badge-success">{{ round($student['score'], 2) }}%</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- PÁGINA NUEVA PARA ESTUDIANTES EN RIESGO --}}
    @if(count($groupStats['estudiantes_riesgo']) > 0)
    <div class="page-break"></div>
    
    <div class="section-title">🚨 ESTUDIANTES EN RIESGO</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Puntuación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupStats['estudiantes_riesgo'] as $student)
            <tr>
                <td>{{ $student['id'] }}</td>
                <td>{{ $student['nombre'] }}</td>
                <td>
                    <span class="badge badge-danger">{{ $student['score'] }}%</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- PIE DE PÁGINA --}}
    <div class="footer">
        <p>Generado automáticamente por el Sistema de Tutoría Virtual Inteligente</p>
        <p>Página {PAGENO} de {nb}</p>
    </div>
</body>
</html>