<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AdminUserController as AdminUserController;
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
    
    // Rutas para Estudiantes
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::post('/update-activity', [StudentDashboardController::class, 'updateActivity'])->name('update-activity');
        
        // Diagnósticos
        Route::prefix('diagnostics')->name('diagnostics.')->group(function () {
            Route::get('/', [StudentDiagnosticController::class, 'index'])->name('index');
            Route::get('/{diagnostic}', [StudentDiagnosticController::class, 'show'])->name('show');
            Route::post('/{diagnostic}/submit', [StudentDiagnosticController::class, 'submit'])->name('submit');
            Route::get('/{diagnostic}/result', [StudentDiagnosticController::class, 'result'])->name('result');
        });
        
        // Contenidos
        Route::prefix('content')->name('content.')->group(function () {
            Route::get('/', [StudentContentController::class, 'index'])->name('index');
            Route::get('/{content}', [StudentContentController::class, 'show'])->name('show');
            Route::post('/{content}/complete', [StudentContentController::class, 'markAsComplete'])->name('complete');
        });
        
        // Progreso
        Route::prefix('progress')->name('progress.')->group(function () {
            Route::get('/', [StudentProgressController::class, 'index'])->name('index');
            Route::get('/subject/{subject}', [StudentProgressController::class, 'bySubject'])->name('by-subject');
        });
        
        // Rutas de aprendizaje
        Route::prefix('learning-paths')->name('learning-paths.')->group(function () {
            Route::get('/', [StudentContentController::class, 'learningPaths'])->name('index');
            Route::get('/{learningPath}', [StudentContentController::class, 'showLearningPath'])->name('show');
        });
        
        // Recomendaciones
        Route::prefix('recommendations')->name('recommendations.')->group(function () {
            Route::get('/', [StudentContentController::class, 'recommendations'])->name('index');
            Route::post('/{recommendation}/mark-viewed', [StudentContentController::class, 'markRecommendationViewed'])->name('mark-viewed');
        });
    });

    // Rutas para Administradores
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::patch('users/{user}/update-role', [AdminUserController::class, 'updateRole'])->name('users.update-role');
        Route::patch('users/{user}/assign-role', [AdminUserController::class, 'assignRole'])->name('users.assign-role');
        // Dentro del grupo Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {  
    Route::get('/ml/resultados', [App\Http\Controllers\Admin\MLAnalysisController::class, 'showResults'])->name('ml.results');

        // Gestión de estudiantes
        Route::resource('students', AdminStudentController::class);
        Route::get('/students/export', [AdminStudentController::class, 'export'])->name('students.export');
        
        // Gestión completa de usuarios
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/pending', [AdminUserController::class, 'pending'])->name('pending');
            Route::post('/bulk-assign-role', [AdminUserController::class, 'bulkAssignRole'])->name('bulk-assign-role');
            Route::get('/export', [AdminUserController::class, 'export'])->name('export');
            Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
            Route::patch('/{user}/role', [AdminUserController::class, 'updateRole'])->name('update-role');
        });

        // Gestión de roles
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/users', [RoleController::class, 'getUsers'])->name('users');
            Route::post('/assign', [RoleController::class, 'assignRole'])->name('assign');
            Route::post('/assign-massive', [RoleController::class, 'assignMassiveRoles'])->name('assign.massive');
            Route::post('/remove', [RoleController::class, 'removeRole'])->name('remove');
        });
        
        // Configuración de cuenta
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
    Route::put('/profile', [App\Http\Controllers\Admin\SettingsController::class, 'updateProfile'])->name('update-profile');
    Route::put('/password', [App\Http\Controllers\Admin\SettingsController::class, 'updatePassword'])->name('update-password');
});
        // Gestión de diagnósticos
        Route::resource('diagnostics', AdminDiagnosticController::class);
        Route::prefix('diagnostics/{diagnostic}/questions')->name('diagnostics.questions.')->group(function () {
            Route::get('/', [AdminDiagnosticController::class, 'questionsIndex'])->name('index');
            Route::get('/create', [AdminDiagnosticController::class, 'questionsCreate'])->name('create');
            Route::post('/', [AdminDiagnosticController::class, 'questionsStore'])->name('store');
            Route::get('/{question}/edit', [AdminDiagnosticController::class, 'questionsEdit'])->name('edit');
            Route::put('/{question}', [AdminDiagnosticController::class, 'questionsUpdate'])->name('update');
            Route::delete('/{question}', [AdminDiagnosticController::class, 'questionsDestroy'])->name('destroy');
        });
        
        // Gestión de contenidos
        Route::resource('content', AdminContentController::class);
        Route::post('/content/bulk-upload', [AdminContentController::class, 'bulkUpload'])->name('content.bulk-upload');
        
        // Reportes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AdminDashboardController::class, 'reports'])->name('index');
            Route::get('/students', [AdminDashboardController::class, 'studentReports'])->name('students');
            Route::get('/performance', [AdminDashboardController::class, 'performanceReports'])->name('performance');
            Route::get('/risk', [AdminDashboardController::class, 'riskReports'])->name('risk');
            Route::post('/generate', [AdminDashboardController::class, 'generateReport'])->name('generate');
        
            // Acciones para estudiantes en riesgo
            Route::post('/send-email/{user}', [AdminDashboardController::class, 'sendEmail'])->name('send-email');
            Route::post('/schedule-followup/{user}', [AdminDashboardController::class, 'scheduleFollowUp'])->name('schedule-followup');

        
        });
        
        // Monitoreo del sistema
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/', [AdminDashboardController::class, 'systemMonitoring'])->name('index');
            Route::get('/usage', [AdminDashboardController::class, 'usageStats'])->name('usage');
        });

    });

    // Rutas para Docentes
    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
        
        // Seguimiento de estudiantes
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [TeacherStudentController::class, 'index'])->name('index');
            Route::get('/{student}', [TeacherStudentController::class, 'show'])->name('show');
            Route::post('/{student}/recommend', [TeacherStudentController::class, 'recommendContent'])->name('recommend');
        });
        
        // Alertas
        Route::prefix('alerts')->name('alerts.')->group(function () {
            Route::get('/', [TeacherDashboardController::class, 'alerts'])->name('index');
            Route::post('/{alert}/resolve', [TeacherDashboardController::class, 'resolveAlert'])->name('resolve');
        });
        
        // Reportes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [TeacherDashboardController::class, 'reports'])->name('index');
            Route::get('/group', [TeacherDashboardController::class, 'groupReports'])->name('group');
            Route::post('/generate', [TeacherDashboardController::class, 'generateReport'])->name('generate');
        });
    });

    // Rutas para el sistema de IA/Predicción
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::post('/predict-difficulties', [PredictionController::class, 'predictDifficulties'])->name('predict-difficulties');
        Route::post('/generate-recommendations', [PredictionController::class, 'generateRecommendations'])->name('generate-recommendations');
        Route::post('/update-learning-path', [PredictionController::class, 'updateLearningPath'])->name('update-learning-path');
    });

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::post('/admin/ml/analyze/{user}', [MLAnalysisController::class, 'analyzeStudent'])
            ->name('admin.ml.analyze');
        
        Route::post('/admin/ml/analyze-all', [MLAnalysisController::class, 'analyzeAll'])
            ->name('admin.ml.analyzeAll');
    });

    
});