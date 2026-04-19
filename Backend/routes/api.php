<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EstudianteController;
use App\Http\Controllers\Api\AeronaveController;
use App\Http\Controllers\Api\BitacoraVueloController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\ProgramaController;
use App\Http\Controllers\Api\MatriculaController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\InstructorController;
use App\Http\Controllers\Api\SmsController;
use App\Http\Controllers\Api\CumplimientoController;
use App\Http\Controllers\Api\VencimientoController;
use App\Http\Controllers\Api\AulaVirtualController;
use App\Http\Controllers\Api\MateriaController;

/*
|--------------------------------------------------------------------------
| API Routes — Escuela de Aviación RAC 141 Colombia
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::post('auth/login',          [AuthController::class, 'login']);
    Route::post('auth/forgot-password',[AuthController::class, 'forgotPassword']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);
});

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    Route::post('auth/logout',   [AuthController::class, 'logout']);
    Route::get('auth/me',        [AuthController::class, 'me']);

    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::prefix('vencimientos')->group(function () {
        Route::get('/',            [VencimientoController::class, 'index']);
        Route::get('criticos',     [VencimientoController::class, 'criticos']);
        Route::get('vencidos',     [VencimientoController::class, 'vencidos']);
        Route::post('sincronizar', [VencimientoController::class, 'sincronizar']);
    });

    Route::prefix('aeronaves')->group(function () {
        Route::get('/',                     [AeronaveController::class, 'index']);
        Route::get('{id}',                  [AeronaveController::class, 'show']);
        Route::get('{id}/mantenimientos',   [AeronaveController::class, 'mantenimientos']); // RAC 43
        Route::get('{id}/mel',              [AeronaveController::class, 'mel']);
        Route::get('{id}/ads',              [AeronaveController::class, 'ads']);
        Route::get('{id}/bitacora-tecnica', [AeronaveController::class, 'bitacoraTecnica']); 
    });

    Route::prefix('instructores')->group(function () {
        Route::get('/',            [InstructorController::class, 'index']);
        Route::get('{id}',         [InstructorController::class, 'show']);
    });

    Route::prefix('programas')->group(function () {
        Route::get('/',          [ProgramaController::class, 'index']);
        Route::post('/',         [ProgramaController::class, 'store']);
        Route::put('{id}',       [ProgramaController::class, 'update']);
    });

    Route::prefix('usuarios')->group(function () {
        Route::get('/',          [UsuarioController::class, 'index']);
        Route::get('roles',      [UsuarioController::class, 'roles']);
        Route::post('/',         [UsuarioController::class, 'store']);
        Route::put('{id}',       [UsuarioController::class, 'update']);
        Route::post('{id}/reset',[UsuarioController::class, 'resetPassword']);
    });

    Route::get('auditoria', [UsuarioController::class, 'auditoria']);

    Route::prefix('estudiantes')->group(function () {
        Route::get('/',              [EstudianteController::class, 'index']);
        Route::post('/',             [EstudianteController::class, 'store']);
        Route::get('{id}',           [EstudianteController::class, 'show']);
        Route::put('{id}',           [EstudianteController::class, 'update']);
        Route::get('{id}/expediente',[EstudianteController::class, 'expediente']);
        Route::get('{id}/progreso-horas', [EstudianteController::class, 'progresoHoras']);
        Route::get('{id}/historial-horas',[EstudianteController::class, 'historialHoras']);
        Route::get('{id}/validar-examen', [EstudianteController::class, 'validarExamen']);
        Route::get('{id}/notas',       [EstudianteController::class, 'notas']);
        Route::post('{id}/notas',      [EstudianteController::class, 'storeNota']);
        Route::get('{id}/cert-medicos',[EstudianteController::class, 'certMedicos']);
        Route::post('{id}/cert-medicos',[EstudianteController::class, 'storeCertMedico']);
        Route::get('{id}/reservas',    [EstudianteController::class, 'reservas']);
        Route::get('{id}/bitacoras',   [EstudianteController::class, 'bitacoras']);
    });

    Route::prefix('bitacoras')->group(function () {
        Route::get('/',            [BitacoraVueloController::class, 'index']);
        Route::post('/',           [BitacoraVueloController::class, 'store']);
        Route::get('{id}',         [BitacoraVueloController::class, 'show']);
    });

    Route::prefix('cumplimiento')->group(function () {
        Route::get('documentos',     [CumplimientoController::class, 'documentos']);
        Route::get('audit-log',      [CumplimientoController::class, 'auditLog']);
        Route::get('checklist-rac141', [CumplimientoController::class, 'checklistRac141']);
    });

    Route::prefix('sms')->group(function () {
        Route::get('reportes',              [SmsController::class, 'index']);
        Route::post('reportes',             [SmsController::class, 'store']);
        Route::get('reportes/{id}',         [SmsController::class, 'show']);
        Route::put('reportes/{id}',         [SmsController::class, 'update']);
        Route::post('reportes/{id}/acciones', [SmsController::class, 'asignarAcciones']);
        Route::post('reportes/{id}/cerrar', [SmsController::class, 'cerrar']);
        
        Route::get('acciones',              [SmsController::class, 'acciones']);
        Route::put('acciones/{id}',         [SmsController::class, 'updateAccion']);
        Route::post('acciones/{id}/cerrar', [SmsController::class, 'cerrarAccion']);
        
        Route::get('dashboard',             [SmsController::class, 'dashboard']);
        Route::get('matriz-riesgo',         [SmsController::class, 'matrizRiesgo']);
    });

    Route::prefix('facturas')->group(function () {
        Route::get('cartera/vencida', [FacturaController::class, 'carteraVencida']);
        Route::get('proximo-numero', [FacturaController::class, 'proximoNumero']);
        Route::get('/',              [FacturaController::class, 'index']);
        Route::post('/',             [FacturaController::class, 'store']);
        Route::get('{id}',           [FacturaController::class, 'show']);
        Route::put('{id}',           [FacturaController::class, 'update']);
        Route::get('{id}/pdf',       [FacturaController::class, 'pdf']);
        Route::get('{id}/pagos',     [FacturaController::class, 'pagos']);
        Route::post('{id}/pagos',    [FacturaController::class, 'storePago']);
    });

    Route::prefix('matriculas')->group(function () {
        Route::get('/',              [MatriculaController::class, 'index']);
        Route::post('/',             [MatriculaController::class, 'store']);
        Route::get('{id}',           [MatriculaController::class, 'show']);
        Route::put('{id}',           [MatriculaController::class, 'update']);
        Route::get('{id}/facturas',  [MatriculaController::class, 'facturas']);
    });

    // --- Aula Virtual (LMS) ---
    Route::prefix('aula-virtual')->group(function () {
        Route::get('mis-materias',        [AulaVirtualController::class, 'misMaterias']);
        Route::get('materia/{id}',        [AulaVirtualController::class, 'detalleMateria']);
        Route::get('materia/{id}/examen', [AulaVirtualController::class, 'generarExamen']);
        Route::post('materia/{id}/examen',[AulaVirtualController::class, 'calificarExamen']);
    });

    // --- Gestión Académica Avanzada (Instructores/Admin) ---
    Route::prefix('gestion-materias')->group(function () {
        Route::put('{id}/lms',              [MateriaController::class, 'updateLms']);
        Route::get('{id}/preguntas',        [MateriaController::class, 'preguntas']);
        Route::post('{id}/preguntas',       [MateriaController::class, 'storePregunta']);
        Route::put('{id}/preguntas/{preg}', [MateriaController::class, 'updatePregunta']);
        Route::delete('{id}/preguntas/{preg}', [MateriaController::class, 'destroyPregunta']);
    });
});
