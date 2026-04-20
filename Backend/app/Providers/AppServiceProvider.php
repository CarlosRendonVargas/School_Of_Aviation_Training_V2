<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Models
use App\Models\BitacoraVuelo;
use App\Models\Estudiante;
use App\Models\Aeronave;
use App\Models\ReporteSms;

// Policies
use App\Policies\BitacoraVueloPolicy;
use App\Policies\EstudiantePolicy;
use App\Policies\AeronavePolicy;

// Services
use App\Services\VencimientoService;
use App\Services\HorasVueloService;
use App\Services\AuditService;
use App\Services\SmsService;
use App\Services\ReservaService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registro de Policies para autorización RBAC
     * basada en roles RAC 141.
     */
    protected $policies = [
        BitacoraVuelo::class => BitacoraVueloPolicy::class,
        Estudiante::class    => EstudiantePolicy::class,
        Aeronave::class      => AeronavePolicy::class,
    ];

    public function register(): void
    {
        // Services como singletons (una sola instancia por request)
        $this->app->singleton(VencimientoService::class);
        $this->app->singleton(HorasVueloService::class);
        $this->app->singleton(AuditService::class);
        $this->app->singleton(SmsService::class);
        $this->app->singleton(ReservaService::class);
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
        
        // Registrar policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Registrar Observadores de Auditoría (RAC 141.77)
        $auditModels = [
            \App\Models\BitacoraVuelo::class,
            \App\Models\Estudiante::class,
            \App\Models\Aeronave::class,
            \App\Models\ReporteSms::class,
            \App\Models\Reserva::class,
            \App\Models\NotaAcademica::class,
            \App\Models\Instructor::class,
            \App\Models\Usuario::class,
        ];

        foreach ($auditModels as $model) {
            $model::observe(\App\Observers\AuditObserver::class);
        }

        // Gate global: dir_ops y auditor_uaeac pueden verlo todo (bypass)
        Gate::before(function ($user, $ability) {
            // auditor_uaeac tiene acceso de solo lectura total
            if ($user->rol?->nombre === 'auditor_uaeac' && str_starts_with($ability, 'view')) {
                return true;
            }
            // dir_ops tiene acceso completo
            if ($user->rol?->nombre === 'dir_ops') {
                return true;
            }
        });
    }
}
