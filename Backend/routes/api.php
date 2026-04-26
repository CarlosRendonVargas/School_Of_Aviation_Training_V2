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
use App\Http\Controllers\Api\MisionVueloController;
use App\Http\Controllers\PlanClaseController;
use App\Http\Controllers\Api\NormatividadController;
use App\Http\Controllers\Api\CertificadoController;
use App\Http\Controllers\Api\EndorsementController;
use App\Http\Controllers\Api\BriefingController;
use App\Http\Controllers\Api\MensajeController;
use App\Http\Controllers\Api\ProspectoController;
use App\Http\Controllers\Api\EvaluacionInstructorController;
use App\Http\Controllers\Api\CapacitacionSmsController;
use App\Http\Controllers\Api\ErgController;
use App\Http\Controllers\Api\EnmiendaController;
use App\Http\Controllers\Api\NominaController;
use App\Http\Controllers\Api\GastoOperativoController;
use App\Http\Controllers\Api\CorrespondenciaController;

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

    Route::post('auth/logout',          [AuthController::class, 'logout']);
    Route::get('auth/me',               [AuthController::class, 'me']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);

    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::prefix('vencimientos')->group(function () {
        Route::get('/',            [VencimientoController::class, 'index']);
        Route::get('criticos',     [VencimientoController::class, 'criticos']);
        Route::get('vencidos',     [VencimientoController::class, 'vencidos']);
        Route::post('sincronizar', [VencimientoController::class, 'sincronizar']);
    });

    Route::prefix('aeronaves')->group(function () {
        Route::get('/',                         [AeronaveController::class, 'index']);
        Route::post('/',                        [AeronaveController::class, 'store']);
        Route::get('{id}',                      [AeronaveController::class, 'show']);
        Route::put('{id}',                      [AeronaveController::class, 'update']);
        Route::get('{id}/mantenimientos',       [AeronaveController::class, 'mantenimientos']);
        Route::post('{id}/mantenimientos',      [AeronaveController::class, 'storeMantenimiento']);
        Route::get('{id}/mel',                  [AeronaveController::class, 'mel']);
        Route::post('{id}/mel',                 [AeronaveController::class, 'storeMel']);
        Route::put('{id}/mel/{mid}',            [AeronaveController::class, 'updateMel']);
        Route::get('{id}/ads',                  [AeronaveController::class, 'ads']);
        Route::post('{id}/ads',                 [AeronaveController::class, 'storeAd']);
        Route::put('{id}/ads/{aid}',            [AeronaveController::class, 'updateAd']);
        Route::get('{id}/bitacora-tecnica',     [AeronaveController::class, 'bitacoraTecnica']);
        Route::get('{id}/horas-acumuladas',     [AeronaveController::class, 'horasAcumuladas']);
    });

    Route::prefix('instructores')->group(function () {
        Route::get('/',                          [InstructorController::class, 'index']);
        Route::post('/',                         [InstructorController::class, 'store']);
        Route::get('{id}',                       [InstructorController::class, 'show']);
        Route::put('{id}',                       [InstructorController::class, 'update']);
        Route::get('{id}/certificaciones',       [InstructorController::class, 'certificaciones']);
        Route::post('{id}/certificaciones',      [InstructorController::class, 'storeCertificacion']);
        Route::get('{id}/planes-clase',          [InstructorController::class, 'planesClase']);
        Route::post('{id}/planes-clase',         [InstructorController::class, 'storePlanClase']);
    });

    Route::prefix('programas')->group(function () {
        Route::get('/',          [ProgramaController::class, 'index']);
        Route::post('/',         [ProgramaController::class, 'store']);
        Route::put('{id}',       [ProgramaController::class, 'update']);
        Route::post('{id}/etapas', [ProgramaController::class, 'storeEtapa']);
        Route::put('etapas/{id}', [ProgramaController::class, 'updateEtapa']);
        Route::delete('etapas/{id}', [ProgramaController::class, 'destroyEtapa']);
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
        Route::get('/',               [EstudianteController::class, 'index']);
        Route::post('/',              [EstudianteController::class, 'store']);
        Route::get('{id}',            [EstudianteController::class, 'show']);
        Route::put('{id}',            [EstudianteController::class, 'update']);
        Route::get('{id}/expediente', [EstudianteController::class, 'expediente']);
        Route::get('{id}/progreso-horas',  [EstudianteController::class, 'progresoHoras']);
        Route::get('{id}/historial-horas', [EstudianteController::class, 'historialHoras']);
        Route::get('{id}/validar-examen',  [EstudianteController::class, 'validarExamen']);
        Route::get('{id}/notas',           [EstudianteController::class, 'notas']);
        Route::post('{id}/notas',          [EstudianteController::class, 'storeNota']);
        Route::get('{id}/cert-medicos',    [EstudianteController::class, 'certMedicos']);
        Route::post('{id}/cert-medicos',   [EstudianteController::class, 'storeCertMedico']);
        Route::get('{id}/reservas',        [EstudianteController::class, 'reservas']);
        Route::get('{id}/bitacoras',       [EstudianteController::class, 'bitacoras']);
    });

    Route::prefix('bitacoras')->group(function () {
        Route::get('/',            [BitacoraVueloController::class, 'index']);
        Route::post('/',           [BitacoraVueloController::class, 'store']);
        Route::get('{id}',         [BitacoraVueloController::class, 'show']);
        Route::put('{id}',         [BitacoraVueloController::class, 'update']);
        Route::post('{id}/firmar', [BitacoraVueloController::class, 'firmar']);
        Route::get('{id}/pdf',     [BitacoraVueloController::class, 'exportarPdf']);
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
        Route::get('exportar-griaa',        [SmsController::class, 'exportarGriaa']);
        Route::get('kpis-aaer',             [SmsController::class, 'kpisAaer']);
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

    Route::prefix('aula-virtual')->group(function () {
        Route::get('mis-materias',        [AulaVirtualController::class, 'misMaterias']);
        Route::get('materia/{id}',        [AulaVirtualController::class, 'detalleMateria']);
        Route::get('materia/{id}/examen', [AulaVirtualController::class, 'generarExamen']);
        Route::post('materia/{id}/examen',[AulaVirtualController::class, 'calificarExamen']);

        Route::get('leccion/{id}/quiz',   [AulaVirtualController::class, 'generarQuizLeccion']);
        Route::post('leccion/{id}/quiz',  [AulaVirtualController::class, 'calificarQuizLeccion']);
        
        Route::get('reporte-notas',       [AulaVirtualController::class, 'todasLasNotas']);
        Route::post('autorizar-reintento', [AulaVirtualController::class, 'autorizarReintento']);
    });

    Route::get('materias', [MateriaController::class, 'index']);

    Route::prefix('gestion-materias')->group(function () {
        Route::put('{id}/lms',              [MateriaController::class, 'updateLms']);
        Route::get('{id}/preguntas',        [MateriaController::class, 'preguntas']);
        Route::post('{id}/preguntas',       [MateriaController::class, 'storePregunta']);
        Route::put('{id}/preguntas/{preg}', [MateriaController::class, 'updatePregunta']);
        Route::delete('{id}/preguntas/{preg}', [MateriaController::class, 'destroyPregunta']);

        Route::get('{id}/lecciones',        [MateriaController::class, 'lecciones']);
        Route::post('{id}/lecciones',       [MateriaController::class, 'storeLeccion']);
        Route::put('{id}/lecciones/{lec}',   [MateriaController::class, 'updateLeccion']);
        Route::delete('{id}/lecciones/{lec}', [MateriaController::class, 'destroyLeccion']);
        
        Route::post('/',         [MateriaController::class, 'store']);
        Route::put('{id}',       [MateriaController::class, 'update']);
        Route::delete('{id}',    [MateriaController::class, 'destroy']);
    });

    Route::get('vuelos',         [MisionVueloController::class, 'index']);
    Route::post('vuelos',        [MisionVueloController::class, 'store']);
    Route::delete('vuelos/{id}', [MisionVueloController::class, 'destroy']);

    Route::prefix('reservas')->group(function () {
        Route::get('/',              [ReservaController::class, 'index']);
        Route::post('/',             [ReservaController::class, 'store']);
        Route::get('calendario',     [ReservaController::class, 'calendario']);
        Route::get('disponibilidad', [ReservaController::class, 'disponibilidad']);
        Route::get('cronograma',     [ReservaController::class, 'cronograma']);
        Route::get('{id}',           [ReservaController::class, 'show']);
        Route::put('{id}',           [ReservaController::class, 'update']);
        Route::post('{id}/confirmar', [ReservaController::class, 'confirmar']);
        Route::post('{id}/cancelar',  [ReservaController::class, 'cancelar']);
        Route::post('{id}/aceptar',   [ReservaController::class, 'aceptarVuelo']);
        Route::post('{id}/rechazar',  [ReservaController::class, 'rechazarVuelo']);
    });

    // --- Planes de Clase ---
    Route::apiResource('planes-clase', PlanClaseController::class);

    // --- Normatividad ---
    Route::prefix('normatividad')->group(function () {
        Route::get('/',       [NormatividadController::class, 'index']);
        Route::post('/',      [NormatividadController::class, 'store']);
        Route::put('{id}',    [NormatividadController::class, 'update']);
        Route::delete('{id}', [NormatividadController::class, 'destroy']);
    });

    // --- Certificados y Constancias ---
    Route::prefix('certificados')->group(function () {
        Route::get('/',            [CertificadoController::class, 'index']);
        Route::post('/',           [CertificadoController::class, 'store']);
        Route::get('{id}',         [CertificadoController::class, 'show']);
        Route::get('{id}/pdf',     [CertificadoController::class, 'pdf']);
        Route::post('{id}/anular', [CertificadoController::class, 'anular']);
    });

    // --- Endorsements (1er vuelo solo) ---
    Route::prefix('endorsements')->group(function () {
        Route::get('/',    [EndorsementController::class, 'index']);
        Route::post('/',   [EndorsementController::class, 'store']);
        Route::get('{id}', [EndorsementController::class, 'show']);
    });

    // --- Briefings / Debriefings ---
    Route::prefix('briefings')->group(function () {
        Route::get('/',      [BriefingController::class, 'index']);
        Route::post('/',     [BriefingController::class, 'store']);
        Route::put('{id}',   [BriefingController::class, 'update']);
    });

    // --- Mensajería Interna ---
    Route::prefix('mensajes')->group(function () {
        Route::get('recibidos',         [MensajeController::class, 'recibidos']);
        Route::get('enviados',          [MensajeController::class, 'enviados']);
        Route::get('no-leidos',         [MensajeController::class, 'noLeidos']);
        Route::get('usuarios',          [MensajeController::class, 'usuarios']);
        Route::post('/',                [MensajeController::class, 'store']);
        Route::get('{id}/hilo',         [MensajeController::class, 'hilo']);
        Route::post('{id}/marcar-leido',[MensajeController::class, 'marcarLeido']);
        Route::delete('{id}',           [MensajeController::class, 'destroy']);
    });

    // --- CRM Prospectos ---
    Route::prefix('prospectos')->group(function () {
        Route::get('estadisticas', [ProspectoController::class, 'estadisticas']);
        Route::get('/',            [ProspectoController::class, 'index']);
        Route::post('/',           [ProspectoController::class, 'store']);
        Route::put('{id}',         [ProspectoController::class, 'update']);
        Route::delete('{id}',      [ProspectoController::class, 'destroy']);
    });

    // --- Evaluaciones de Instructor ---
    Route::prefix('evaluaciones-instructor')->group(function () {
        Route::get('/',                          [EvaluacionInstructorController::class, 'index']);
        Route::post('/',                         [EvaluacionInstructorController::class, 'store']);
        Route::get('instructor/{id}/historial',  [EvaluacionInstructorController::class, 'historialInstructor']);
    });

    // --- Capacitaciones SMS ---
    Route::prefix('capacitaciones-sms')->group(function () {
        Route::get('/',                          [CapacitacionSmsController::class, 'index']);
        Route::post('/',                         [CapacitacionSmsController::class, 'store']);
        Route::put('{id}',                       [CapacitacionSmsController::class, 'update']);
        Route::get('{id}/registros',             [CapacitacionSmsController::class, 'registros']);
        Route::post('{id}/asistencia',           [CapacitacionSmsController::class, 'registrarAsistencia']);
    });

    // --- Plan de Respuesta a Emergencias (ERG) ---
    Route::prefix('erg')->group(function () {
        Route::get('/',       [ErgController::class, 'index']);
        Route::post('/',      [ErgController::class, 'store']);
        Route::put('{id}',    [ErgController::class, 'update']);
        Route::delete('{id}', [ErgController::class, 'destroy']);
    });

    // --- Enmiendas MOE/PIA ---
    Route::prefix('enmiendas')->group(function () {
        Route::get('/',       [EnmiendaController::class, 'index']);
        Route::post('/',      [EnmiendaController::class, 'store']);
        Route::put('{id}',    [EnmiendaController::class, 'update']);
    });

    // --- Nómina ---
    Route::prefix('nomina')->group(function () {
        Route::get('periodos',              [NominaController::class, 'periodos']);
        Route::post('periodos',             [NominaController::class, 'storePeriodo']);
        Route::get('periodos/{id}/items',   [NominaController::class, 'itemsPeriodo']);
        Route::post('periodos/{id}/items',  [NominaController::class, 'storeItem']);
        Route::post('periodos/{id}/cerrar', [NominaController::class, 'cerrarPeriodo']);
    });

    // --- Gastos Operativos / Caja Menor ---
    Route::prefix('gastos')->group(function () {
        Route::get('resumen', [GastoOperativoController::class, 'resumen']);
        Route::get('/',       [GastoOperativoController::class, 'index']);
        Route::post('/',      [GastoOperativoController::class, 'store']);
    });

    // --- Correspondencia UAEAC ---
    Route::prefix('correspondencia')->group(function () {
        Route::get('estadisticas', [CorrespondenciaController::class, 'estadisticas']);
        Route::get('/',            [CorrespondenciaController::class, 'index']);
        Route::post('/',           [CorrespondenciaController::class, 'store']);
        Route::get('{id}',         [CorrespondenciaController::class, 'show']);
        Route::put('{id}',         [CorrespondenciaController::class, 'update']);
    });
});