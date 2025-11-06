<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Student\DiagnosticController as StudentDiagnosticController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ContentController as StudentContentController;
use App\Http\Controllers\Student\ProgressController as StudentProgressController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\DiagnosticController as AdminDiagnosticController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\StudentProgressController as TeacherStudentController;
use App\Http\Controllers\Admin\FollowUpController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AI\PredictionController;
use App\Http\Controllers\Admin\MLAnalysisController;

Auth::routes();

Route::get('/', function () {
    if (Auth::check()) {
        /** @var User $user */
        $user = Auth::user();
        
        if ($user && $user instanceof User) {
            if ($user->isStudent()) {
                return redirect()->route('student.dashboard');
            } elseif ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->isTeacher()) {
                return redirect()->route('teacher.dashboard');
            }
        }
    }
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    
    // ========================================
    // RUTAS PARA ESTUDIANTES
    // ========================================
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::post('/update-activity', [StudentDashboardController::class, 'updateActivity'])->name('update-activity');
        
        // Diagnósticos (cambiado {diagnostic} por {id})
        Route::prefix('diagnostics')->name('diagnostics.')->group(function () {
            Route::get('/', [StudentDiagnosticController::class, 'index'])->name('index');
            Route::get('/{id}', [StudentDiagnosticController::class, 'show'])->name('show');
            Route::post('/{id}/submit', [StudentDiagnosticController::class, 'submit'])->name('submit');
            Route::get('/{id}/result', [StudentDiagnosticController::class, 'result'])->name('result');
        });
        
        // Contenidos (cambiado {content} por {id})
        Route::prefix('content')->name('content.')->group(function () {
            Route::get('/', [StudentContentController::class, 'index'])->name('index');
            Route::get('/{id}', [StudentContentController::class, 'show'])->name('show');
            Route::post('/{id}/complete', [StudentContentController::class, 'markAsComplete'])->name('complete');
        });
        
        // Progreso
        Route::prefix('progress')->name('progress.')->group(function () {
            Route::get('/', [StudentProgressController::class, 'index'])->name('index');
            Route::get('/subject/{subject}', [StudentProgressController::class, 'bySubject'])->name('by-subject');
        });
        
        // Rutas de aprendizaje (cambiado {learningPath} por {id})
        Route::prefix('learning-paths')->name('learning-paths.')->group(function () {
            Route::get('/', [StudentContentController::class, 'learningPaths'])->name('index');
            Route::get('/{id}', [StudentContentController::class, 'showLearningPath'])->name('show');
        });
        
        // Recomendaciones (cambiado {recommendation} por {id})
        Route::prefix('recommendations')->name('recommendations.')->group(function () {
            Route::get('/', [StudentContentController::class, 'recommendations'])->name('index');
            Route::post('/{id}/mark-viewed', [StudentContentController::class, 'markRecommendationViewed'])->name('mark-viewed');
            Route::get('/ml-dashboard', [RecommendationController::class, 'dashboard'])->name('ml.dashboard');
            Route::post('/ml/update-profile', [RecommendationController::class, 'updateProfile'])->name('ml.update-profile');
        });

    });

    // ========================================
    // RUTAS PARA ADMINISTRADORES
    // ========================================
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // ML Analysis
        Route::get('/ml/resultados', [MLAnalysisController::class, 'showResults'])->name('ml.results');
        Route::post('/ml/analyze/{id}', [MLAnalysisController::class, 'analyzeStudent'])->name('ml.analyze');
        Route::post('/ml/analyze-all', [MLAnalysisController::class, 'analyzeAll'])->name('ml.analyzeAll');

        // ========================================
        // Gestión de estudiantes (CAMBIADO A PDO)
        // ========================================
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [AdminStudentController::class, 'index'])->name('index');
            Route::get('/create', [AdminStudentController::class, 'create'])->name('create');
            Route::post('/', [AdminStudentController::class, 'store'])->name('store');
            Route::get('/export', [AdminStudentController::class, 'export'])->name('export');
            Route::get('/{id}', [AdminStudentController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [AdminStudentController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AdminStudentController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminStudentController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/follow-ups', [FollowUpController::class, 'studentFollowUps'])->name('follow-ups');
        });
        
        // ========================================
        // Gestión completa de usuarios
        // ========================================
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/pending', [AdminUserController::class, 'pending'])->name('pending');
            Route::post('/bulk-assign-role', [AdminUserController::class, 'bulkAssignRole'])->name('bulk-assign-role');
            Route::get('/export', [AdminUserController::class, 'export'])->name('export');
            Route::get('/{id}', [AdminUserController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminUserController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/role', [AdminUserController::class, 'updateRole'])->name('update-role');
            Route::patch('/{id}/assign-role', [AdminUserController::class, 'assignRole'])->name('assign-role');
        });

        // ========================================
        // Gestión de roles
        // ========================================
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/users', [RoleController::class, 'getUsers'])->name('users');
            Route::post('/assign', [RoleController::class, 'assignRole'])->name('assign');
            Route::post('/assign-massive', [RoleController::class, 'assignMassiveRoles'])->name('assign.massive');
            Route::post('/remove', [RoleController::class, 'removeRole'])->name('remove');
        });
        
        // ========================================
        // Configuración de cuenta
        // ========================================
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('update-profile');
            Route::put('/password', [SettingsController::class, 'updatePassword'])->name('update-password');
        });
        
        // ========================================
        // Gestión de diagnósticos (cambiado {diagnostic} y {question} por {id})
        // ========================================
        Route::prefix('diagnostics')->name('diagnostics.')->group(function () {
            Route::get('/', [AdminDiagnosticController::class, 'index'])->name('index');
            Route::get('/create', [AdminDiagnosticController::class, 'create'])->name('create');
            Route::post('/', [AdminDiagnosticController::class, 'store'])->name('store');
            Route::get('/{id}', [AdminDiagnosticController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [AdminDiagnosticController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AdminDiagnosticController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminDiagnosticController::class, 'destroy'])->name('destroy');
            
            // Preguntas del diagnóstico
            Route::prefix('{diagnostic_id}/questions')->name('questions.')->group(function () {
                Route::get('/', [AdminDiagnosticController::class, 'questionsIndex'])->name('index');
                Route::get('/create', [AdminDiagnosticController::class, 'questionsCreate'])->name('create');
                Route::post('/', [AdminDiagnosticController::class, 'questionsStore'])->name('store');
                Route::get('/{question_id}/edit', [AdminDiagnosticController::class, 'questionsEdit'])->name('edit');
                Route::put('/{question_id}', [AdminDiagnosticController::class, 'questionsUpdate'])->name('update');
                Route::delete('/{question_id}', [AdminDiagnosticController::class, 'questionsDestroy'])->name('destroy');
            });
        });
        
        // ========================================
        // Gestión de contenidos (cambiado {content} por {id})
        // ========================================
        Route::prefix('content')->name('content.')->group(function () {
            Route::get('/', [AdminContentController::class, 'index'])->name('index');
            Route::get('/create', [AdminContentController::class, 'create'])->name('create');
            Route::post('/', [AdminContentController::class, 'store'])->name('store');
            Route::post('/bulk-upload', [AdminContentController::class, 'bulkUpload'])->name('bulk-upload');
            Route::get('/{id}', [AdminContentController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [AdminContentController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AdminContentController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminContentController::class, 'destroy'])->name('destroy');
        });
        
        // ========================================
        // Reportes (AGREGADO ReportController)
        // ========================================
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('dashboard');
            Route::get('/students', [ReportController::class, 'students'])->name('students');
            Route::get('/performance', [ReportController::class, 'performance'])->name('performance');
            Route::get('/risk', [ReportController::class, 'risk'])->name('risk');
            Route::get('/content', [ReportController::class, 'content'])->name('content');
            Route::get('/progress', [ReportController::class, 'progress'])->name('progress');
            Route::get('/ml-analysis', [ReportController::class, 'mlAnalysis'])->name('ml-analysis');
            
            // Exportaciones
            Route::get('/export/csv', [ReportController::class, 'exportCsv'])->name('export.csv');
            Route::get('/export/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
            
            // Acciones para estudiantes en riesgo
            Route::post('/send-email/{id}', [AdminDashboardController::class, 'sendEmail'])->name('send-email');
            Route::post('/schedule-followup/{id}', [AdminDashboardController::class, 'scheduleFollowUp'])->name('schedule-followup');
        });
        
        // ========================================
        // Seguimientos (Follow-ups)
        // ========================================
        Route::prefix('follow-ups')->name('follow-ups.')->group(function () {
            Route::get('/', [FollowUpController::class, 'index'])->name('index');
            Route::get('/create', [FollowUpController::class, 'create'])->name('create');
            Route::post('/', [FollowUpController::class, 'store'])->name('store');
            Route::get('/upcoming', [FollowUpController::class, 'upcoming'])->name('upcoming');
            Route::get('/{id}', [FollowUpController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [FollowUpController::class, 'edit'])->name('edit');
            Route::put('/{id}', [FollowUpController::class, 'update'])->name('update');
            Route::delete('/{id}', [FollowUpController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/complete', [FollowUpController::class, 'complete'])->name('complete');
            Route::post('/{id}/cancel', [FollowUpController::class, 'cancel'])->name('cancel');
        });
        
        // ========================================
        // Monitoreo del sistema
        // ========================================
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/', [AdminDashboardController::class, 'systemMonitoring'])->name('index');
            Route::get('/usage', [AdminDashboardController::class, 'usageStats'])->name('usage');
        });
    });

    // ========================================
    // RUTAS PARA DOCENTES
    // ========================================
    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
        
        // Seguimiento de estudiantes (cambiado {student} por {id})
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [TeacherStudentController::class, 'index'])->name('index');
            Route::get('/{id}', [TeacherStudentController::class, 'show'])->name('show');
            Route::post('/{id}/recommend', [TeacherStudentController::class, 'recommendContent'])->name('recommend');
        });
        
        // Alertas (cambiado {alert} por {id})
        Route::prefix('alerts')->name('alerts.')->group(function () {
            Route::get('/', [TeacherDashboardController::class, 'alerts'])->name('index');
            Route::post('/{id}/resolve', [TeacherDashboardController::class, 'resolveAlert'])->name('resolve');
        });
        
        // Reportes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [TeacherDashboardController::class, 'reports'])->name('index');
            Route::get('/group', [TeacherDashboardController::class, 'groupReports'])->name('group');
            Route::post('/generate', [TeacherDashboardController::class, 'generateReport'])->name('generate');
        });
    });

    // ========================================
    // RUTAS PARA IA/PREDICCIÓN
    // ========================================
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::post('/predict-difficulties', [PredictionController::class, 'predictDifficulties'])->name('predict-difficulties');
        Route::post('/generate-recommendations', [PredictionController::class, 'generateRecommendations'])->name('generate-recommendations');
        Route::post('/update-learning-path', [PredictionController::class, 'updateLearningPath'])->name('update-learning-path');
    });
});