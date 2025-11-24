{{-- resources/views/admin/reports/risk-report-pdf.blade.php --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe Automatizado de Riesgo</title>
    <style>
        /* Estilos Generales */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            padding: 20px;
        }
        
        /* Encabezado (bg-danger en web) */
        .header {
            background-color: white;
            color: #dc3545; /* Color principal rojo del riesgo */
            padding: 25px 20px;
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 4px solid #dc3545; /* Borde rojo fuerte */
        }
        
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 0.5px;
            line-height: 1.3;
            color: #dc3545 !important;
        }
        
        .header p {
            margin: 0;
            font-size: 13px;
            font-weight: 500;
            color: #6c757d !important;
        }
        
        /* Caja de Información (border-info en web) */
        .info-box {
            background-color: #e2f4ff; /* Azul muy claro para info */
            border-left: 4px solid #007bff; /* Borde azul de info */
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box h3 {
            color: #007bff;
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        
        .info-row > div {
            display: table-cell;
            padding: 4px;
        }
        
        .info-label {
            font-weight: bold;
            width: 40%;
            color: #495057;
        }

        /* Criterios de Riesgo (alert-info en web) */
        .criteria-box {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .criteria-box strong {
            display: block;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .criteria-box ul {
            margin: 0 0 0 20px;
            padding: 0;
        }
        
        .criteria-box li {
            margin-bottom: 5px;
            line-height: 1.5;
        }
        
        /* Resumen Ejecutivo (Grid/Cards en web) */
        .section-title {
            background-color: #495057;
            color: white;
            padding: 12px 15px;
            margin-top: 25px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stats-cell {
            display: table-cell;
            width: 20%;
            padding: 18px 10px;
            text-align: center;
            border: 1px solid #dee2e6; /* Borde general */
            background-color: #f8f9fa;
            vertical-align: middle;
        }
        
        .stats-cell.total {
             /* Card border-info en web */
            border-color: #007bff;
        }
        
        .stats-cell.risk-total {
            /* Card border-danger en web */
            border-color: #dc3545;
            background-color: #f8d7da; /* Fondo sutil para total riesgo */
        }
        
        .stats-cell.critical {
            /* bg-danger en web */
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        .stats-cell.warning {
            /* bg-warning en web */
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529; /* Texto oscuro para contraste */
        }
        
        .stats-cell.secondary {
             /* bg-secondary en web */
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }
        
        .stats-cell h2 {
            margin: 0 0 8px 0;
            font-size: 32px;
            color: inherit; /* Hereda el color de la caja */
            font-weight: bold;
        }
        
        .stats-cell p {
            margin: 0;
            font-size: 10px;
            color: inherit;
            text-transform: uppercase;
            font-weight: 600;
            line-height: 1.4;
        }

        /* Recomendaciones (Alerts en web) */
        .recommendation-box {
            background-color: #eaf5ff; /* Color de info */
            border-left: 4px solid #007bff;
            padding: 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
            border-radius: 3px;
        }
        
        .recommendation-box.urgent {
            /* alert-danger en web */
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }
        
        .recommendation-box.high {
             /* alert-warning en web */
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }
        
        .recommendation-box h4 {
            margin: 0 0 6px 0;
            font-size: 11px;
            color: #004085; /* Texto oscuro para info */
            font-weight: bold;
        }
        
        .recommendation-box.urgent h4 {
            color: #721c24; /* Texto oscuro para danger */
        }
        
        .recommendation-box.high h4 {
            color: #856404; /* Texto oscuro para warning */
        }
        
        .recommendation-box p {
            margin: 0 0 4px 0;
            font-size: 10px;
            line-height: 1.4;
        }
        
        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 10px;
        }
        
        th {
            /* table-dark en web */
            background-color: #343a40;
            color: white;
            padding: 10px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        
        td {
            padding: 8px 6px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa; /* Estilo hover sutil */
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            line-height: 1;
        }
        
        .badge-danger {
            background-color: #dc3545; /* bg-danger */
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107; /* bg-warning */
            color: #212529; /* Texto oscuro para contraste */
        }
        
        .badge-secondary {
            background-color: #6c757d; /* bg-secondary */
            color: white;
        }
        
        .badge-dark {
            background-color: #343a40; /* bg-dark */
            color: white;
        }
        
        .risk-factors {
            font-size: 9px;
            line-height: 1.4;
        }
        
        .risk-factors li {
            margin-bottom: 4px;
        }
        
        /* Nota Final */
        .note-box {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 3px;
        }
        
        .note-box strong {
            display: block;
            margin-bottom: 8px;
            font-size: 12px;
            color: #007bff;
        }
        
        .note-box p {
            margin: 0;
            line-height: 1.5;
        }
        
        /* Pie de Página */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
            border-top: 2px solid #dee2e6;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    {{-- ENCABEZADO (ROJO Y NEGRITA) --}}
    <div class="header">
        <h1>INFORME AUTOMATIZADO DE RIESGO</h1>
        <p>Sistema de Tutoría Virtual Inteligente - Reporte Semanal de Estudiantes</p>
    </div>

    {{-- INFORMACIÓN DEL REPORTE (BOX AZUL CLARO) --}}
    <div class="info-box">
        <h3>Información del Reporte</h3>
        <div class="info-row">
            <div class="info-label">Fecha de generación:</div>
            <div>{{ $reportStats['generation_date'] }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Período:</div>
            <div>Semana {{ $reportStats['report_week'] }} del {{ $reportStats['report_year'] }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total estudiantes sistema:</div>
            <div>{{ $reportStats['total_students'] }}</div>
        </div>
    </div>

    {{-- CRITERIOS DE RIESGO (BOX INFO) --}}
    <div class="criteria-box">
        <strong><i style="color: #007bff;">&#9432;</i> Criterios de Riesgo Aplicados:</strong>
        <ul>
            <li>Puntaje menor al <strong>{{ $riskCriteria['low_score_threshold'] }}%</strong> en evaluaciones diagnósticas</li>
            <li>Falta de actividad durante más de <strong>{{ $riskCriteria['inactivity_days'] }} días</strong></li>
            <li>Menos de <strong>{{ $riskCriteria['min_activities'] }} actividades</strong> completadas</li>
        </ul>
    </div>

    {{-- RESUMEN EJECUTIVO (TÍTULO GRIS OSCURO) --}}
    <div class="section-title">RESUMEN EJECUTIVO</div>
    
    {{-- STATS GRID (REPLICANDO LAS TARJETAS DE LA WEB) --}}
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stats-cell total" style="color: #007bff;">
                <h2>{{ $reportStats['total_students'] }}</h2>
                <p>Total<br>Estudiantes</p>
            </div>
            <div class="stats-cell risk-total" style="color: #dc3545; font-weight: bold;">
                <h2>{{ $reportStats['total_risk_students'] }}</h2>
                <p>En Riesgo<br>({{ $reportStats['percentage_at_risk'] }}%)</p>
            </div>
            <div class="stats-cell critical">
                <h2>{{ $reportStats['critical'] }}</h2>
                <p>Nivel<br>Crítico</p>
            </div>
            <div class="stats-cell warning">
                <h2>{{ $reportStats['high'] }}</h2>
                <p>Nivel<br>Alto</p>
            </div>
            <div class="stats-cell secondary">
                <h2>{{ $reportStats['moderate'] }}</h2>
                <p>Nivel<br>Moderado</p>
            </div>
        </div>
    </div>

    {{-- RECOMENDACIONES AUTOMÁTICAS --}}
    @if(count($recommendations) > 0)
    <div class="section-title">💡 RECOMENDACIONES DE ACCIÓN PRIORITARIAS</div>
    
    @foreach($recommendations as $rec)
    @php
        $priorityClass = $rec['priority'] == 'urgent' ? 'urgent' : ($rec['priority'] == 'high' ? 'high' : '');
        $priorityText = $rec['priority'] == 'urgent' ? '🚨 URGENTE' : ($rec['priority'] == 'high' ? '⚠️ ALTA PRIORIDAD' : '📌 PRIORIDAD MEDIA');
    @endphp
    <div class="recommendation-box {{ $priorityClass }}">
        <h4>
            {{ $priorityText }} - {{ $rec['title'] }}
        </h4>
        <p>{{ $rec['description'] }}</p>
        <p style="margin-top: 5px; color: #6c757d;">
            <strong>Estudiantes afectados:</strong> {{ count($rec['students']) }}
        </p>
    </div>
    @endforeach
    @endif

    {{-- TABLA DE ESTUDIANTES EN RIESGO CRÍTICO --}}
    @php
        // Filtra los estudiantes por categoría
        $criticalStudents = collect($riskStudents)->where('risk_category', 'critico');
    @endphp
    
    @if($criticalStudents->count() > 0)
    <div class="page-break"></div>
    
    <div class="section-title" style="background-color: #dc3545;">🔴 ESTUDIANTES EN RIESGO CRÍTICO ({{ $criticalStudents->count() }})</div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Estudiante</th>
                <th style="width: 15%;">Carrera/Sem</th>
                <th style="width: 8%;">Prom.</th>
                <th style="width: 8%;">Act.</th>
                <th style="width: 12%;">Último Acceso</th>
                <th style="width: 37%;">Factores de Riesgo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($criticalStudents as $student)
            <tr>
                <td>
                    <strong>{{ $student['nombre'] }}</strong><br>
                    <span style="font-size: 9px; color: #6c757d;">{{ $student['email'] }}</span>
                </td>
                
                <td>
                    {{ Str::limit($student['carrera'], 18) }}<br>
                    <span style="font-size: 9px;">Sem: {{ $student['semestre'] }}</span>
                </td>
                
                <td style="text-align: center;">
                    <span class="badge badge-danger">
                        {{ $student['avg_score'] }}%
                    </span>
                </td>
                
                <td style="text-align: center;">
                    {{ $student['total_activities'] }}
                </td>
                
                <td>
                    @if($student['last_activity'])
                        {{ \Carbon\Carbon::parse($student['last_activity'])->format('d/m/Y') }}<br>
                        <span style="color: #dc3545; font-weight: bold;">
                            ({{ $student['days_inactive'] }} días)
                        </span>
                    @else
                        <span class="badge badge-dark">Sin actividad</span>
                    @endif
                </td>
                
                <td>
                    <ul class="risk-factors" style="list-style: none; padding: 0; margin: 0;">
                        @foreach($student['risk_factors'] as $factor)
                        <li>
                            • <strong>{{ $factor['factor'] }}:</strong> {{ $factor['detail'] }}
                        </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ESTUDIANTES EN RIESGO ALTO Y MODERADO --}}
    @php
        // Filtra los estudiantes por categoría Alto o Moderado
        $otherRiskStudents = collect($riskStudents)->whereIn('risk_category', ['alto', 'moderado']);
    @endphp
    
    @if($otherRiskStudents->count() > 0)
    <div class="section-title" style="background-color: #ffc107; color: #333;">🟠 ESTUDIANTES EN RIESGO ALTO Y MODERADO ({{ $otherRiskStudents->count() }})</div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Nivel</th>
                <th style="width: 18%;">Estudiante</th>
                <th style="width: 15%;">Carrera/Sem</th>
                <th style="width: 8%;">Prom.</th>
                <th style="width: 7%;">Act.</th>
                <th style="width: 12%;">Último Acceso</th>
                <th style="width: 30%;">Factores de Riesgo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($otherRiskStudents as $student)
            <tr>
                <td>
                    @if($student['risk_category'] == 'alto')
                        <span class="badge badge-warning">ALTO</span>
                    @else
                        <span class="badge badge-secondary">MODERADO</span>
                    @endif
                </td>
                
                <td>
                    <strong>{{ Str::limit($student['nombre'], 20) }}</strong><br>
                    <span style="font-size: 8px; color: #6c757d;">{{ Str::limit($student['email'], 25) }}</span>
                </td>
                
                <td>
                    {{ Str::limit($student['carrera'], 18) }}<br>
                    <span style="font-size: 9px;">Sem: {{ $student['semestre'] }}</span>
                </td>
                
                <td style="text-align: center;">
                    {{-- Usa danger si es muy bajo, warning si es bajo --}}
                    <span class="badge badge-{{ $student['avg_score'] < 40 ? 'danger' : 'warning' }}">
                        {{ $student['avg_score'] }}%
                    </span>
                </td>
                
                <td style="text-align: center;">
                    {{ $student['total_activities'] }}
                </td>
                
                <td>
                    @if($student['last_activity'])
                        {{ \Carbon\Carbon::parse($student['last_activity'])->format('d/m/Y') }}<br>
                        <span style="font-size: 9px; color: #dc3545;">
                            ({{ $student['days_inactive'] }}d)
                        </span>
                    @else
                        <span class="badge badge-dark">N/A</span>
                    @endif
                </td>
                
                <td>
                    <ul class="risk-factors" style="list-style: none; padding: 0; margin: 0;">
                        {{-- En la tabla de Alto/Moderado solo mostramos el factor principal por espacio --}}
                        @foreach($student['risk_factors'] as $factor)
                        <li>• {{ $factor['factor'] }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- NOTA FINAL --}}
    <div class="note-box">
        <strong>📌 Nota Importante:</strong>
        <p>
            Este informe ha sido generado automáticamente por el sistema. Se recomienda realizar un seguimiento personalizado 
            con cada estudiante identificado en riesgo crítico dentro de las próximas <strong>48 horas</strong>.
        </p>
    </div>

    {{-- PIE DE PÁGINA --}}
    <div class="footer">
        <p>
            <strong>Sistema de Tutoría Virtual Inteligente</strong> | 
            Informe Automatizado de Riesgo | 
            Generado: {{ $reportStats['generation_date'] }}
        </p>
    </div>
</body>
</html>