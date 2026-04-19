# ✈️ Sistema Informático Escuela de Aviación — RAC 141 Colombia

Sistema de gestión académica, operacional y regulatoria para escuelas de aviación
certificadas por la **UAEAC** bajo el **Reglamento Aeronáutico de Colombia RAC 141**.

---

## 📋 Requisitos del Sistema

| Componente   | Versión mínima |
|---|---|
| PHP          | 8.2+           |
| Laravel      | 12.x           |
| MySQL        | 8.0+ / PostgreSQL 15+ |
| Composer     | 2.x            |
| Node.js      | 20+            |

---

## 🚀 Instalación Paso a Paso

### 1. Clonar e instalar dependencias

```bash
git clone https://github.com/tu-escuela/rac141-system.git
cd rac141-system

composer install
npm install
```

### 2. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env`:

```env
APP_NAME="Escuela Aviación RAC141"
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Bogota
APP_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rac141_db
DB_USERNAME=root
DB_PASSWORD=tu_password

# Sanctum (API tokens)
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

### 3. Crear base de datos

```bash
mysql -u root -p -e "CREATE DATABASE rac141_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4. Ejecutar migraciones y seeders

```bash
# Ejecutar las 29 migraciones en orden
php artisan migrate

# Poblar datos iniciales (roles, permisos, programas RAC 61, aeronaves)
php artisan db:seed
```

### 5. Instalar Laravel Sanctum (autenticación API)

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Agregar en `app/Models/Usuario.php`:
```php
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Model
{
    use HasApiTokens;
    // ...
}
```

### 6. Dependencias opcionales recomendadas

```bash
# PDF para exportar bitácoras y documentos UAEAC
composer require barryvdh/laravel-dompdf

# Permisos avanzados (complemento a las Policies)
composer require spatie/laravel-permission

# Exportar Excel (reportes UAEAC)
composer require maatwebsite/excel

# Queues para envío de alertas de vencimiento
php artisan queue:table
php artisan migrate
```

### 7. Iniciar servidor de desarrollo

```bash
php artisan serve
# API disponible en: http://localhost:8000/api/v1
```

---

## 🗂️ Estructura del Proyecto

```
app/
├── Http/Controllers/Api/
│   ├── AuthController.php          # Login, logout, reset password
│   ├── DashboardController.php     # Dashboard adaptado por rol
│   ├── EstudianteController.php    # CRUD + expediente + horas RAC 61
│   ├── InstructorController.php    # CRUD + certificaciones RAC 65
│   ├── AeronaveController.php      # CRUD + MEL + ADs + mantenimiento
│   ├── BitacoraVueloController.php # Bitácoras con firma digital
│   ├── ReservaController.php       # Scheduling con validaciones RAC
│   ├── SmsController.php           # SMS OACI Anexo 19
│   ├── VencimientoController.php   # Panel central de alertas
│   ├── CumplimientoController.php  # MOE, PIA, audit log
│   ├── MatriculaController.php     # Matrículas y contratos
│   ├── FacturaController.php       # Facturación DIAN
│   ├── ProgramaController.php      # Programas RAC 141.67
│   ├── BancoPreguntasController.php# Exámenes estilo UAEAC
│   └── ReporteController.php       # Reportes y exportaciones
│
├── Models/                         # 23 modelos Eloquent
├── Policies/                       # Autorización RBAC por rol
│   ├── BitacoraVueloPolicy.php
│   ├── EstudiantePolicy.php
│   └── AeronavePolicy.php
│
├── Services/                       # Lógica de negocio RAC
│   ├── VencimientoService.php      # Motor de alertas
│   ├── HorasVueloService.php       # Verificación mínimos RAC 61
│   ├── AuditService.php            # Trazabilidad RAC 141.77
│   ├── SmsService.php              # SMS OACI Anexo 19
│   └── ReservaService.php          # Validaciones pre-vuelo
│
├── Providers/
│   └── AppServiceProvider.php      # Registro de Policies y Services
│
database/
├── migrations/                     # 29 migraciones en orden
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── RolesUsuariosSeeder.php     # 6 roles + usuario admin
│   ├── ProgramasSeeder.php         # PPL, CPL, ATPL, IFR, Multimotor
│   └── AeronavesSeeder.php         # 4 aeronaves de ejemplo
│
routes/
└── api.php                         # 60+ endpoints organizados por módulo
```

---

## 🔐 Autenticación y Roles

### Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@escuela.com",
  "password": "password"
}
```

### Usar el token
```http
GET /api/v1/dashboard
Authorization: Bearer {token}
```

### Roles disponibles

| Rol | Acceso |
|---|---|
| `estudiante` | Sus propios datos, progreso, exámenes |
| `instructor` | Sus estudiantes, bitácoras, planes de clase |
| `admin` | Matrículas, facturación, usuarios |
| `dir_ops` | Acceso completo al sistema |
| `mantenimiento` | Flota, MEL, ADs, mantenimientos |
| `auditor_uaeac` | Solo lectura total (para inspecciones) |

---

## 📡 Endpoints Principales

### Módulo Académico
| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/v1/estudiantes/{id}/progreso-horas` | Horas acumuladas vs. mínimos RAC 61 |
| GET | `/api/v1/estudiantes/{id}/validar-examen` | Verifica si cumple requisitos UAEAC |
| GET | `/api/v1/estudiantes/{id}/expediente` | Expediente completo RAC 141.77 |
| POST | `/api/v1/notas` | Registrar calificación de materia |

### Módulo Vuelo
| Método | Endpoint | Descripción |
|---|---|---|
| POST | `/api/v1/bitacoras` | Nueva entrada de bitácora |
| POST | `/api/v1/bitacoras/{id}/firmar` | Firma digital instructor/estudiante |
| POST | `/api/v1/reservas` | Crear reserva (con validaciones RAC) |
| GET | `/api/v1/reservas/disponibilidad` | Aeronaves disponibles por slot |

### SMS (Seguridad)
| Método | Endpoint | Descripción |
|---|---|---|
| POST | `/api/v1/sms/reportes` | Crear reporte (puede ser anónimo) |
| GET | `/api/v1/sms/dashboard` | KPIs del SMS |
| GET | `/api/v1/sms/matriz-riesgo` | Matriz OACI 5×5 |

---

## ⏰ Tareas Programadas (Scheduler)

Agregar en `app/Console/Kernel.php` o con el nuevo scheduler de Laravel 12:

```php
// config/console.php (Laravel 12 style)
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    app(\App\Services\VencimientoService::class)->sincronizar();
})->dailyAt('06:00')->description('Sincronizar vencimientos críticos');

// Marcar facturas vencidas
Schedule::command('facturas:marcar-vencidas')->daily();
```

Activar el scheduler en cron:
```bash
* * * * * cd /ruta/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📊 Cumplimiento RAC 141 → Módulos del Sistema

| Requisito RAC | Módulo | Implementado |
|---|---|---|
| RAC 141.11 – Certificación escuela | Cumplimiento / documentos_rac | ✔ |
| RAC 141.13 – Manual de Operaciones (MOE) | Cumplimiento / documentos_rac | ✔ |
| RAC 141.31 – Director de Operaciones | Gestión de Instructores | ✔ |
| RAC 141.35 – Instructores certificados | Instructores / cert_instructores | ✔ |
| RAC 141.51 – Aeronaves aptas | Aeronaves / venc_airworthiness | ✔ |
| RAC 141.67 – Programa de instrucción (PIA) | Programas / etapas / materias | ✔ |
| RAC 141.71 – Registros de instrucción | Bitácoras de vuelo | ✔ |
| RAC 141.73 – Evaluaciones de progreso | Notas académicas | ✔ |
| RAC 141.77 – Conservar registros 5 años | audit_log + todos los módulos | ✔ |
| RAC 61 – Horas mínimas por licencia | HorasVueloService | ✔ |
| RAC 65 – Certificación instructores | cert_instructores + alertas | ✔ |
| RAC 67 – Certificado médico | cert_medicos + validación reserva | ✔ |
| RAC 43 – Mantenimiento aeronaves | registros_mantenimiento | ✔ |
| OACI Anexo 19 – SMS | reportes_sms / acciones_correctivas | ✔ |

---

## 👤 Usuario administrador inicial

Creado por el seeder:
```
Email:    admin@escuela.com
Password: Admin2025*
Rol:      dir_ops
```

> ⚠️ **Cambiar la contraseña después del primer login.**

---

## 📞 Soporte

- Regulación de referencia: [RAC UAEAC](https://www.aerocivil.gov.co/normatividad/RAC)
- OACI Anexo 19 (SMS): [icao.int](https://www.icao.int)
