<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Microlearning Platform') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 2px 0;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .progress-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .alert-risk {
            border-left: 4px solid #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }
        .alert-warning {
            border-left: 4px solid #ffc107;
            background: rgba(255, 193, 7, 0.1);
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <nav class="col-md-2 d-none d-md-block sidebar">
                    <div class="sidebar-sticky p-3">
                        <h5 class="text-white mb-4">
                            <i class="fas fa-graduation-cap"></i> UC Learning
                        </h5>
                        
                        @auth
                        <div class="text-center mb-4">
                            <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-user text-primary fa-2x"></i>
                            </div>
                            <p class="text-white mt-2 mb-1">{{ Auth::user()->name }}</p>
                            
                            @if(Auth::user()->role)
                                @if(Auth::user()->isAdmin())
                                    <span class="badge bg-danger">
                                        <i class="fas fa-crown me-1"></i>{{ Auth::user()->role->name }}
                                    </span>
                                @elseif(Auth::user()->isTeacher())
                                    <span class="badge bg-success">
                                        <i class="fas fa-chalkboard-teacher me-1"></i>{{ Auth::user()->role->name }}
                                    </span>
                                @elseif(Auth::user()->isStudent())
                                    <span class="badge bg-info">
                                        <i class="fas fa-user-graduate me-1"></i>{{ Auth::user()->role->name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">{{ Auth::user()->role->name }}</span>
                                @endif
                            @else
                                <small class="text-light">Usuario</small>
                            @endif
                        </div>

                            <ul class="nav flex-column">
                                @if(Auth::user()->isStudent())
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('student.dashboard') }}">
                                            <i class="fas fa-tachometer-alt"></i> Dashboard
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('student.diagnostics.index') }}">
                                            <i class="fas fa-clipboard-check"></i> Diagnósticos
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('student.content.index') }}">
                                            <i class="fas fa-book"></i> Contenidos
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('student.progress.index') }}">
                                            <i class="fas fa-chart-line"></i> Mi Progreso
                                        </a>
                                    </li>
                                @elseif(Auth::user()->isAdmin())
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                            <i class="fas fa-tachometer-alt"></i> Dashboard
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('admin.students.index') }}">
                                            <i class="fas fa-users"></i> Estudiantes
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('admin.diagnostics.index') }}">
                                            <i class="fas fa-clipboard-list"></i> Diagnósticos
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('admin.content.index') }}">
                                            <i class="fas fa-folder"></i> Biblioteca
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('admin.ml.results') }}">
                                            <i class="fas fa-brain"></i> Análisis ML
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('admin.reports.index') }}">
                                            <i class="fas fa-chart-bar"></i> Reportes
                                        </a>
                                    </li>
                                @elseif(Auth::user()->isTeacher())
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('teacher.dashboard') }}">
                                            <i class="fas fa-tachometer-alt"></i> Dashboard
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('teacher.students.index') }}">
                                            <i class="fas fa-user-graduate"></i> Mis Estudiantes
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('teacher.alerts.index') }}">
                                            <i class="fas fa-exclamation-triangle"></i> Alertas
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('teacher.reports.index') }}">
                                            <i class="fas fa-file-alt"></i> Reportes
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endauth
                        
                        <hr class="bg-light">
                        
                        @auth
                            <a class="nav-link" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        @endauth
                    </div>
                </nav>

                <!-- Main content -->
                <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                    <div class="py-4">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    {{-- <script src="{{ asset('js/app.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('scripts')
</body>
</html>