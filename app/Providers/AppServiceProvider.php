<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Importar interfaces
use App\Contracts\UserDAOInterface;
use App\Contracts\FollowUpDAOInterface;
use App\Contracts\DiagnosticResponseDAOInterface;
use App\Contracts\DiagnosticDAOInterface;
use App\Contracts\StudentProgressDAOInterface;

// Importar implementaciones
use App\DataAccessObjects\EloquentUserDAO;
use App\DataAccessObjects\EloquentFollowUpDAO;
use App\DataAccessObjects\EloquentDiagnosticResponseDAO;
use App\DataAccessObjects\EloquentDiagnosticDAO;
use App\DataAccessObjects\EloquentStudentProgressDAO;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar bindings de DAOs
        // Cuando se pida la interface, Laravel inyectará la implementación
        
        $this->app->bind(
            UserDAOInterface::class,
            EloquentUserDAO::class
        );

        $this->app->bind(
            FollowUpDAOInterface::class,
            EloquentFollowUpDAO::class
        );

        $this->app->bind(
            DiagnosticResponseDAOInterface::class,
            EloquentDiagnosticResponseDAO::class
        );

        $this->app->bind(
            DiagnosticDAOInterface::class,
            EloquentDiagnosticDAO::class
        );

        $this->app->bind(
            StudentProgressDAOInterface::class,
            EloquentStudentProgressDAO::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}