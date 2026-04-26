# MANUAL DE USO — SISTEMA DE GESTIÓN AERONÁUTICA RAC 141

### School of Aviation Training V2

**Versión:** 1.0 · **Fecha:** Abril 2026 · **Regulación:** RAC 61 / 65 / 91 / 141 · UAEAC Colombia

---

## TABLA DE CONTENIDO

1. [Introducción](#1-introducción)
2. [Requisitos e Instalación](#2-requisitos-e-instalación)
3. [Primer Arranque](#3-primer-arranque)
4. [Roles y Permisos](#4-roles-y-permisos)
5. [Inicio de Sesión y Navegación](#5-inicio-de-sesión-y-navegación)
6. [Módulo: Administrador (admin)](#6-módulo-administrador)
7. [Módulo: Director de Operaciones (dir_ops)](#7-módulo-director-de-operaciones)
8. [Módulo: Instructor](#8-módulo-instructor)
9. [Módulo: Estudiante](#9-módulo-estudiante)
10. [Módulo: Mantenimiento](#10-módulo-mantenimiento)
11. [Módulo: Auditor UAEAC](#11-módulo-auditor-uaeac)
12. [Flujos de Trabajo Críticos](#12-flujos-de-trabajo-críticos)
13. [Alertas y Vencimientos](#13-alertas-y-vencimientos)
14. [Resolución de Problemas Comunes](#14-resolución-de-problemas-comunes)

---

## 1. INTRODUCCIÓN

Este sistema es una plataforma integral de gestión para escuelas de aviación certificadas bajo **RAC 141** en Colombia. Cubre todos los procesos operativos:

- Gestión académica (programas PIA, materias, exámenes, Aula Virtual)
- Operaciones de vuelo (reservas, bitácoras, briefings)
- Flota y mantenimiento (RAC 43, MEL, Airworthiness Directives)
- Personal (instructores RAC 65, estudiantes RAC 61)
- Seguridad operacional (SMS, ERG, capacitaciones)
- Cumplimiento regulatorio (UAEAC, enmiendas, correspondencia)
- Administración y finanzas (matrículas, facturación, nómina)
- Mensajería interna y certificados

---

## 2. REQUISITOS E INSTALACIÓN

### 2.1 Requisitos del Servidor

| Componente | Versión mínima                         |
| ---------- | -------------------------------------- |
| PHP        | 8.2+                                   |
| MySQL      | 8.0+ (puerto 3307 por defecto en WAMP) |
| Composer   | 2.x                                    |
| Node.js    | 18+                                    |
| npm        | 9+                                     |

### 2.2 Instalación del Backend (Laravel)

```bash
# 1. Entrar al directorio del backend
cd Backend

# 2. Instalar dependencias PHP
composer install

# 3. Copiar y configurar el archivo de entorno
cp .env.example .env

# 4. Editar .env con los datos de la base de datos
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3307
# DB_DATABASE=school_aviation
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Generar la clave de aplicación
php artisan key:generate

# 6. Ejecutar migraciones
php artisan migrate

# 7. Poblar la base de datos con datos iniciales
php artisan db:seed

# 8. Crear enlace de almacenamiento (para archivos PDF/imágenes)
php artisan storage:link

# 9. Iniciar el servidor de desarrollo
php artisan serve --port=8000
```

### 2.3 Instalación del Frontend (Quasar/Vue)

```bash
# 1. Entrar al directorio del frontend
cd Frontend

# 2. Instalar dependencias JavaScript
npm install

# 3. Configurar la URL del API en .env o quasar.config.js
# API_URL=http://localhost:8000/api/v1

# 4. Iniciar en modo desarrollo
npm run dev
# O para producción:
npm run build
```

### 2.4 Acceso al Sistema

Una vez arrancados ambos servidores, abrir el navegador en:

```
http://localhost:9000
```

---

## 3. PRIMER ARRANQUE

### 3.1 Credenciales Iniciales (Seeder)

Después de ejecutar `php artisan db:seed`, el sistema crea los siguientes usuarios de prueba:

| Rol             | Email                     | Contraseña |
| --------------- | ------------------------- | ---------- |
| Administrador   | admin@escuela.com         | password   |
| Director de Ops | director@escuela.com      | password   |
| Instructor      | instructor@escuela.com    | password   |
| Estudiante      | estudiante@escuela.com    | password   |
| Mantenimiento   | mantenimiento@escuela.com | password   |
| Auditor UAEAC   | auditor@escuela.com       | password   |

> **IMPORTANTE:** Cambiar todas las contraseñas inmediatamente en producción desde `Seguridad > Usuarios`.

### 3.2 Configuración Inicial Obligatoria

Seguir este orden estrictamente:

```
1. Crear Roles y Usuarios (Seguridad)
2. Registrar Programas PIA (Académico > Programas)
3. Registrar Aeronaves de la Flota (Aeronaves)
4. Registrar Instructores (Instructores)
5. Registrar Estudiantes y Matrículas (Académico > Estudiantes)
6. Cargar banco de preguntas por materia (Académico > Gestión Materias)
7. Programar primera Reserva de Vuelo (Reservas)
```

---

## 4. ROLES Y PERMISOS

El sistema tiene **6 roles** con acceso diferenciado:

### 4.1 Tabla de Acceso por Módulo

| Módulo                | admin | dir_ops | instructor | estudiante | mantenimiento | auditor_uaeac |
| --------------------- | :---: | :-----: | :--------: | :--------: | :-----------: | :-----------: |
| Dashboard             |  ✅  |   ✅    |     ✅     |     ✅     |      ✅       |      ✅       |
| Seguridad / Usuarios  |  ✅   |    —    |     —      |     —      |       —       |       —       |
| Académico (gestión)   |  👁️   |   ✅    |     ✅     |     —      |       —       |      👁️       |
| Aula Virtual (LMS)    |   —   |    —    |     —      |     ✅     |       —       |       —       |
| Vuelo / Bitácoras     |   —   |   ✅    |     ✅     |     👁️     |      👁️       |      👁️       |
| Reservas / Calendario |  ✅   |   ✅    |     ✅     |     ✅     |       —       |       —       |
| Instructores          |   —   |   ✅    |     —      |     —      |       —       |      👁️       |
| Aeronaves             |   —   |   ✅    |     —      |     —      |      ✅       |      👁️       |
| Mantenimiento         |   —   |   ✅    |     —      |     —      |      ✅       |      👁️       |
| SMS                   |  ✅   |   ✅    |     ✅     |     ✅     |       —       |      👁️       |
| Cumplimiento          |   —   |   ✅    |     —      |     —      |      . —       |      👁️       |
| Normatividad          |  ✅   |   ✅    |     ✅     |     ✅     |      ✅       |      ✅       |
| Certificados          |  ✅   |   ✅    |     ✅     |     👁️     |       —       |      👁️       |
| Financiero / Admin    |  ✅   |   ✅    |     —      |     —      |       —       |       —       |
| Matrículas            |  ✅   |   ✅    |     —      |     —      |       —       |       —       |
| Facturación           |  ✅   |   ✅    |     —      |     —      |       —       |       —       |
| Nómina                |  ✅   |   ✅    |     —      |     —      |       —       |       —       |
| Gastos                |  ✅   |   ✅    |     —      |     —      |       —       |       —       |
| Prospectos CRM        |  ✅   |   ✅    |     —      |     —      |       —       |       —       |
| Mensajería            |  ✅   |   ✅    |     ✅     |     ✅     |      ✅       |      ✅       |

**Leyenda:** ✅ Acceso completo · 👁️ Solo lectura · — Sin acceso

---

## 5. INICIO DE SESIÓN Y NAVEGACIÓN

### 5.1 Iniciar Sesión

1. Abrir `http://localhost:9000`
2. Ingresar **correo electrónico** y **contraseña**
3. Clic en **"Iniciar Sesión"**
4. El sistema redirige automáticamente al **Dashboard** del rol correspondiente

### 5.2 Recuperar Contraseña

1. En la pantalla de login, clic en **"¿Olvidó su contraseña?"**
2. Ingresar el correo registrado
3. Revisar el correo electrónico y seguir el enlace recibido
4. Establecer la nueva contraseña

### 5.3 Navegación General

- El **menú lateral izquierdo** muestra solo los módulos habilitados para el rol
- El **icono de campana** en la barra superior muestra alertas de vencimientos críticos
- El **icono de sobre** muestra mensajes internos no leídos
- El **avatar** en la parte superior derecha accede al perfil y cerrar sesión
- En dispositivos móviles, el menú se colapsa con el botón ☰

### 5.4 Cambiar Contraseña Personal

1. Clic en el avatar (esquina superior derecha)
2. Seleccionar **"Mi Perfil"**
3. Ir a la sección **"Cambiar Contraseña"**
4. Ingresar contraseña actual y la nueva contraseña
5. Clic en **"Guardar"**

---

## 6. MÓDULO ADMINISTRADOR

El **admin** gestiona usuarios, finanzas, matrículas y prospección. No tiene acceso a operaciones de vuelo en detalle.

### 6.1 Gestión de Usuarios (Seguridad)

**Ruta:** `Seguridad`

#### Crear un nuevo usuario:

1. Ir a **Seguridad > Usuarios**
2. Clic en **"Nuevo Usuario"**
3. Completar:
   - Nombres y apellidos
   - Correo electrónico (será el login)
   - Rol (admin / dir_ops / instructor / estudiante / mantenimiento / auditor_uaeac)
   - Contraseña temporal
4. Clic en **"Crear"**
5. El sistema crea automáticamente el perfil `Persona` vinculado

#### Resetear contraseña de un usuario:

1. Buscar el usuario en la lista
2. Clic en el menú de acciones (**⋮**)
3. Seleccionar **"Resetear Contraseña"**
4. Confirmar — el sistema envía un correo con el enlace de reset

#### Ver log de auditoría:

1. Ir a **Seguridad > Auditoría**
2. Filtrar por usuario, fecha o tipo de acción
3. Cada registro muestra: quién, qué tabla, qué acción, cuándo

### 6.2 Matrículas

**Ruta:** `Matrículas`

#### Registrar una nueva matrícula:

1. Ir a **Matrículas**
2. Clic en **"Nueva Matrícula"**
3. Seleccionar el **estudiante** (debe existir previamente)
4. Seleccionar el **programa** (PPL, CPL, ATPL, IFR, Multi-motor)
5. Ingresar el **valor total del programa**
6. Definir las condiciones de pago
7. Clic en **"Guardar"**

#### Registrar un pago:

1. Abrir la matrícula del estudiante
2. Ir a la pestaña **"Pagos"**
3. Clic en **"Registrar Pago"**
4. Ingresar: monto, fecha, método de pago
5. El sistema actualiza automáticamente el saldo pendiente

### 6.3 Facturación

**Ruta:** `Facturación`

1. Clic en **"Nueva Factura"**
2. Vincular a la **matrícula** correspondiente
3. El sistema asigna automáticamente el número de factura correlativo
4. Completar los ítems de cobro
5. Clic en **"Generar"** → opción de descargar PDF

### 6.4 Nómina

**Ruta:** `Nómina`

#### Crear período de nómina:

1. Ir a **Nómina**
2. Clic en **"Nuevo Período"**
3. Definir nombre (ej: "Abril 2026"), fecha inicio y fin
4. Agregar ítems: seleccionar empleado, concepto, valor
5. Cuando esté completo, clic en **"Cerrar Período"** → queda en estado "Pagado"

### 6.5 Gastos Operativos

**Ruta:** `Gastos`

1. Clic en **"Registrar Gasto"**
2. Ingresar: descripción, monto, fecha, categoría, responsable
3. El panel muestra el resumen total por categoría

### 6.6 Prospectos (CRM)

**Ruta:** `Prospectos`

1. Clic en **"Nuevo Prospecto"**
2. Registrar: nombre, teléfono, correo, programa de interés
3. Actualizar el **estado** del pipeline a medida que avanza: `nuevo → contactado → en_proceso → matriculado / perdido`
4. Ver estadísticas de conversión en el panel superior

---

## 7. MÓDULO DIRECTOR DE OPERACIONES

El **dir_ops** tiene la autoridad técnica más alta. Gestiona todo el ciclo operativo de la escuela.

### 7.1 Dashboard Director

Al ingresar, el dashboard muestra:

- Total estudiantes activos / instructores activos
- Aeronaves disponibles hoy
- Reservas del día
- Alertas críticas de vencimiento
- Botón **"Sincronizar Alertas"** — ejecutar al inicio de cada día laboral

### 7.2 Gestión Académica

**Ruta:** `Académico`

#### Crear / Editar un Programa PIA:

1. Ir a **Académico > Programas**
2. Clic en **"Nuevo Programa"**
3. Completar:
   - Código (ej: PPL-AV)
   - Nombre del programa
   - Tipo: PPL / CPL / ATPL / HABILITACION
   - Horas mínimas según RAC 61 (tierra, vuelo, dual, solo, IFR, noche, simulador)
   - Referencia RAC aplicable
4. Agregar **etapas** al programa (fases curriculares)
5. Cada etapa contiene **materias** (asignaturas teóricas)

#### Registrar un Endorsement de Vuelo Solo:

1. Ir a **Académico > Endorsements** o al menú `Endorsements`
2. Clic en **"Registrar"**
3. Seleccionar el **estudiante**
4. Tipo de endorsement: Primer Vuelo Solo / Vuelo Solo Área / XC Corto / XC Largo / Nocturno / Examen Vuelo
5. Fecha, matrícula de aeronave, aeropuerto ICAO, observaciones y firma del instructor
6. Clic en **"Guardar"**

#### Gestionar Habilitaciones (Reintentos de Examen):

1. Ir a **Académico > Habilitaciones**
2. Buscar el estudiante que requiere reintento
3. Seleccionar la materia
4. Ingresar el número de recibo del pago en tesorería
5. Clic en **"Autorizar Reintento"**
6. El estudiante podrá presentar el examen nuevamente

### 7.3 Gestión de Instructores

**Ruta:** `Instructores`

#### Registrar un nuevo instructor:

1. Ir a **Instructores**
2. Clic en **"Nuevo Instructor"**
3. Completar datos personales y:
   - Número de licencia
   - Tipo de licencia (PPL/CPL/ATPL)
   - Fecha de vencimiento de licencia
   - Habilitaciones (IFR, Multi-motor, Tipo)
   - Horas PIC totales / horas de instrucción
4. Clic en **"Guardar"**

#### Evaluar a un instructor (RAC 65):

1. Ir a **Evaluaciones Instructor**
2. Clic en **"Nueva Evaluación"**
3. Seleccionar instructor, fecha, resultado y observaciones
4. Las evaluaciones quedan en el historial del instructor

### 7.4 Operaciones de Vuelo

#### Crear una Reserva de Vuelo:

1. Ir a **Reservas**
2. Clic en **"Nueva Reserva"**
3. El sistema valida automáticamente:
   - ✅ Aeronave disponible y aeronavegabilidad vigente
   - ✅ Sin ítems MEL bloqueantes
   - ✅ Estudiante activo con médico vigente (RAC 67)
   - ✅ Instructor con licencia vigente
   - ✅ Sin conflicto de horario
4. Si todas las validaciones pasan, la reserva queda en estado **"Pendiente"**
5. El sistema envía automáticamente un **mensaje interno** al estudiante para que confirme en 24 horas

> **Nota:** Si alguna validación falla, el sistema muestra el error específico y la reserva no se crea.

#### Ver el Calendario de Operaciones:

1. Ir a **Calendario**
2. Navegar por meses con las flechas de navegación
3. Los puntos de colores indican el estado de cada reserva:
   - 🟡 Pendiente · 🟢 Confirmada · 🔵 Completada · 🟣 Virtual · 🔴 Cancelada
4. Clic en cualquier día para ver el **Manifiesto de Vuelo** con detalle de todas las actividades

#### Registrar una Bitácora de Vuelo:

1. Ir a **Vuelo > Nueva Bitácora**
2. Vincular a la **reserva** correspondiente
3. Completar todos los campos del vuelo:
   - Aeropuertos de salida y llegada (código ICAO)
   - Hora de salida y llegada
   - Horas: total, dual, solo, nocturna, IFR, simulador
   - Número de aterrizajes
   - Condiciones VMC/IMC
   - Observaciones
4. **Firma del instructor** (campo firma_instructor)
5. Clic en **"Certificar Misión"** → la bitácora queda registrada y se acumulan las horas del estudiante

### 7.5 Cumplimiento Regulatorio

**Ruta:** `Cumplimiento`

#### Checklist RAC 141:

1. Ir a **Cumplimiento**
2. La pestaña **"Checklist"** muestra todos los requisitos RAC 141 con semáforo de cumplimiento
3. Los ítems en rojo requieren atención inmediata

#### Registrar Correspondencia con UAEAC:

1. Ir a **Cumplimiento > Correspondencia**
2. Clic en **"Nueva Correspondencia"**
3. Tipo: solicitud / respuesta / informe
4. Completar asunto, contenido, adjuntar archivo si aplica
5. Clic en **"Guardar"**

#### Registrar Enmienda de Documento:

1. Ir a **Cumplimiento > Enmiendas**
2. Clic en **"Nueva Enmienda"**
3. Seleccionar el documento afectado, describir los cambios y la fecha efectiva
4. Queda en el historial de control de cambios

---

## 8. MÓDULO INSTRUCTOR

El instructor gestiona la instrucción de vuelo, evalúa estudiantes y registra actividades académicas.

### 8.1 Dashboard Instructor

Al ingresar muestra:

- Estado de su licencia (días para vencimiento)
- Vuelos programados hoy
- Alumnos asignados
- Mensajes no leídos

### 8.2 Programar un Vuelo

1. Ir a **Reservas**
2. Clic en **"Nueva Reserva"** (o desde el perfil del estudiante)
3. El campo **Instructor** se pre-llena con el propio instructor
4. Seleccionar estudiante, aeronave, fecha y horario
5. Agregar objetivos del vuelo
6. Clic en **"Crear"** → el estudiante recibe una notificación para confirmar

### 8.3 Registrar Briefing Pre-Vuelo

1. Ir a la reserva del vuelo programado
2. Clic en **"Briefing Pre-Vuelo"**
3. Registrar: contenido del briefing, áreas revisadas
4. El briefing queda vinculado a la reserva

### 8.4 Registrar Bitácora Después del Vuelo

1. Ir a **Vuelo > Nueva Bitácora**
2. Vincular a la reserva completada
3. Completar horas de vuelo y parámetros del vuelo
4. Registrar **Debriefing Post-Vuelo**
5. Firmar digitalmente la bitácora
6. El sistema acumula las horas al expediente del estudiante

### 8.5 Gestión de Planes de Clase

1. Ir a **Académico > Planes de Clase**
2. Clic en **"Nuevo Plan"**
3. Seleccionar: materia, fecha de clase, tema, objetivos
4. Registrar los resultados de la evaluación posterior

### 8.6 Ver Progreso de un Estudiante

1. Ir a **Estudiantes**
2. Buscar al estudiante por nombre o expediente
3. Clic en **"Ver Expediente"**
4. Ver:
   - Pestaña **"Progreso"**: horas acumuladas vs. mínimos RAC 61
   - Pestaña **"Académico"**: notas por materia
   - Pestaña **"Bitácoras"**: historial de vuelos
   - Pestaña **"Médicos"**: vigencia del certificado médico

### 8.7 Registrar Endorsement de Vuelo Solo

1. Ir a **Endorsements**
2. Clic en **"Registrar"**
3. Seleccionar estudiante, tipo de endorsement, fecha, aeronave y aeropuerto
4. Ingresar firma y observaciones
5. Clic en **"Guardar"**

---

## 9. MÓDULO ESTUDIANTE

El estudiante accede a su programa de instrucción, presenta exámenes y confirma sus vuelos.

### 9.1 Dashboard Estudiante

Al ingresar muestra:

- Vigencia del certificado médico (días restantes)
- Próximos vuelos programados
- Progreso de horas vs. mínimos del programa
- Acceso rápido al Aula Virtual y Mi Progreso

### 9.2 Aula Virtual (LMS)

**Ruta:** `Aula Virtual`

#### Paso a paso para estudiar una materia:

**Paso 1 — Seleccionar la materia:**

1. Ir a **Aula Virtual**
2. Ver el listado de todas las materias del programa con su estado (EN CURSO / APROBADO / HABILITADO)
3. Clic en **"Entrar a Módulo"** en la materia deseada

**Paso 2 — Estudiar las lecciones:**

1. En la pestaña **"Lecciones del Módulo"**, hacer clic en cada lección para expandirla
2. Ver el **video** de la lección (YouTube o MP4)
3. Leer el **material PDF** de referencia RAC (en tablets/desktop se muestra inline; en móvil, botón para abrir)
4. Al terminar cada lección, clic en **"Comenzar Quiz Ahora"**

**Paso 3 — Presentar el Quiz de Lección:**

1. Confirmar el inicio del quiz (advertencia anti-fraude)
2. Responder las 5 preguntas de selección múltiple
3. El temporizador corre (5 minutos por quiz)
4. Clic en **"FINALIZAR"** en la última pregunta
5. El resultado se muestra inmediatamente con la nota obtenida
6. **Si se aprueba (≥75%):** la lección queda marcada con ✅
7. **Si no se aprueba:** se puede reintentar (máximo 3 intentos por defecto)

> ⚠️ **Anti-fraude:** Si el estudiante sale de la ventana durante el quiz, recibe una advertencia. Al segundo intento de salir, el quiz se envía automáticamente.

**Paso 4 — Presentar el Examen Final:**

1. Una vez completadas las lecciones, ir al panel derecho **"Estatus de Certificación"**
2. Si el puntaje promedio de quices es satisfactorio, clic en **"Presentar Examen Final"**
3. Leer el protocolo de evaluación segura y confirmar
4. Responder las 10 preguntas en el tiempo asignado (15 minutos por defecto)
5. Navegar entre preguntas con los botones Anterior / Siguiente
6. En la última pregunta, clic en **"FINALIZAR"**

**Cálculo de la Nota Final (RAC 141):**

```
Nota Final = (Promedio de Quices × 40%) + (Nota Examen Final × 60%)
```

- Si no hay lecciones configuradas: Nota Final = 100% examen
- Nota mínima de aprobación: **75%**

**Paso 5 — Si no aprueba el examen:**

1. El sistema bloquea futuros intentos
2. Clic en **"Solicitar Habilitación de Reintento"**
3. Dirigirse a **Tesorería** para realizar el pago del derecho de reintento
4. Una vez que el director o administrador autorice el reintento en el sistema, el examen se desbloquea automáticamente

#### Asistir a una Sesión Virtual en Vivo:

1. Ir a **Aula Virtual** y entrar a la materia correspondiente
2. Ir a la pestaña **"Briefing en Vivo"**
3. Si hay sesión activa, clic en **"Unirse a la Sesión Ahora"** → abre Google Meet en una nueva pestaña
4. También se puede acceder directamente desde el **Calendario** haciendo clic en la clase virtual del día

### 9.3 Mi Progreso

**Ruta:** `Mi Progreso`

Muestra el avance general en el programa:

- Porcentaje de horas acumuladas vs. mínimos RAC 61
- Desglose por tipo de hora: dual, solo, IFR, nocturna, simulador
- Materias aprobadas / pendientes
- Etapa actual del programa

### 9.4 Confirmar un Plan de Vuelo

Cuando el instructor programa una actividad, el estudiante recibe:

1. Un **mensaje interno** con los detalles del vuelo
2. En **Cronograma** aparece la tarjeta con un contador de 24 horas

Para confirmar:

1. Ir a **Cronograma**
2. Ver la tarjeta del vuelo con el estado **"PENDIENTE CONFIRMACIÓN"**
3. Revisar fecha, hora e instructor
4. Clic en **"Aceptar Plan de Vuelo"** → el vuelo queda confirmado
5. O clic en **"No puedo asistir"** → ingresar motivo y el instructor es notificado

> ⚠️ Si no se confirma dentro de las **24 horas**, el sistema cancela automáticamente la reserva.

### 9.5 Ver el Calendario de Actividades

1. Ir a **Calendario**
2. Ver todos los vuelos programados y sesiones virtuales del mes
3. Clic en un día para ver el **Manifiesto** con los detalles
4. En sesiones virtuales, clic en **"Ir al Aula Virtual"** → navega directamente a la materia y abre la pestaña "En Vivo"

### 9.6 Mensajería Interna

1. Ir a **Mensajes**
2. Pestaña **"Recibidos"**: ver mensajes del instructor / administración
3. Clic en un mensaje para ver el hilo completo y responder
4. El contador de mensajes no leídos aparece en el menú lateral

---

## 10. MÓDULO MANTENIMIENTO

El técnico de mantenimiento gestiona la flota bajo **RAC 43**.

### 10.1 Dashboard Mantenimiento

Muestra:

- Aeronaves con mantenimiento próximo o vencido
- Ítems MEL abiertos
- Airworthiness Directives pendientes

### 10.2 Registrar Mantenimiento a una Aeronave

1. Ir a **Aeronaves**
2. Clic en la aeronave a mantener
3. Ir a la pestaña **"Mantenimiento"**
4. Clic en **"Registrar Mantenimiento"**
5. Completar:
   - Tipo: 100h / 500h / 1000h / Overhaul / AD
   - Descripción del trabajo realizado
   - Técnico responsable
   - Horas de célula y motor al momento del mantenimiento
6. Clic en **"Guardar"**
7. Las horas desde el último overhaul se actualizan automáticamente

### 10.3 Gestionar Ítems MEL

1. Ir a **Aeronaves > Detalle > MEL**
2. **Abrir ítem MEL:**
   - Clic en **"Nuevo Ítem MEL"**
   - Describir el componente inoperativo y la referencia MEL
   - El ítem queda en estado **"Abierto"** → puede restringir operaciones
3. **Cerrar ítem MEL:**
   - Clic en el ítem abierto
   - Seleccionar **"Marcar como Resuelto"** e ingresar la fecha de cierre

### 10.4 Gestionar Airworthiness Directives (ADs)

1. Ir a **Aeronaves > Detalle > ADs**
2. **Registrar AD:**
   - Clic en **"Nueva AD"**
   - Ingresar número de AD, descripción, fecha de vencimiento
   - Estado inicial: **"Pendiente"**
3. **Marcar AD como completada:**
   - Clic en la AD
   - Seleccionar **"Completar"** e ingresar la fecha de cumplimiento

### 10.5 Cambiar Estado de una Aeronave

1. Ir a **Aeronaves**
2. Clic en la aeronave
3. En el campo **Estado**, seleccionar:
   - `disponible` → operativa para vuelos
   - `mantenimiento` → bloqueada para reservas
   - `fuera_de_servicio` → inhabilitada completamente
4. Clic en **"Guardar"**

---

## 11. MÓDULO AUDITOR UAEAC

El auditor tiene acceso de **solo lectura** a todos los módulos regulatorios.

### 11.1 Qué puede ver el Auditor

| Sección      | Información Disponible                                       |
| ------------ | ------------------------------------------------------------ |
| Académico    | Programas PIA, materias, notas, endorsements, certificados   |
| Vuelo        | Todas las bitácoras, horas acumuladas por estudiante         |
| Instructores | Licencias, habilitaciones, evaluaciones RAC 65               |
| Aeronaves    | Estado de flota, mantenimientos, MEL, ADs, aeronavegabilidad |
| SMS          | Reportes de seguridad, acciones correctivas, tendencias      |
| Cumplimiento | Checklist RAC 141, enmiendas, correspondencia UAEAC          |
| Normatividad | Biblioteca de documentos regulatorios                        |

### 11.2 Exportar Reportes para UAEAC

1. En cada módulo, buscar el botón **"Exportar"** o el ícono de descarga
2. Los reportes se generan en formato PDF o Excel según el módulo
3. El reporte GRIAA del SMS se exporta desde: **SMS > Exportar GRIAA**

---

## 12. FLUJOS DE TRABAJO CRÍTICOS

### 12.1 Flujo Completo: Desde Prospecto hasta Graduado

```
PROSPECTO → MATRICULADO → ACTIVO EN PROGRAMA → GRADUADO

1. [Admin] Registrar prospecto en CRM (Prospectos)
2. [Admin] Cuando decide matricularse:
   a. Crear usuario con rol "estudiante" (Seguridad)
   b. Crear matrícula y registrar forma de pago (Matrículas)
3. [Dir_Ops] El estudiante queda activo en el programa
4. [Instructor] Programar vuelos de instrucción (Reservas)
5. [Estudiante] Confirmar vuelos en Cronograma (24h)
6. [Instructor] Ejecutar vuelo + registrar bitácora (Vuelo)
7. [Estudiante] Estudiar materias teóricas en Aula Virtual
8. [Estudiante] Presentar quices y examen final por materia
9. [Dir_Ops] Registrar endorsements de vuelo solo cuando corresponda
10. [Dir_Ops] Al completar horas y materias → emitir Certificado
```

### 12.2 Flujo de una Sesión de Vuelo

```
PROGRAMACIÓN → CONFIRMACIÓN → PRE-VUELO → VUELO → POST-VUELO → REGISTRO

1. [Instructor] Crear reserva en el sistema
2. [Sistema] Valida aeronave, estudiante, instructor, horario automáticamente
3. [Sistema] Notifica al estudiante por mensaje interno
4. [Estudiante] Confirma en Cronograma (máx. 24h)
5. [Instructor] Registra Briefing Pre-Vuelo
6. [Instructor/Estudiante] Ejecutan el vuelo
7. [Instructor] Registra Bitácora de Vuelo con todas las horas
8. [Instructor] Registra Debriefing Post-Vuelo
9. [Sistema] Acumula horas al expediente del estudiante automáticamente
10. [Sistema] Actualiza estado de reserva a "Completada"
```

### 12.3 Flujo de Examen Virtual (Aula Virtual)

```
ESTUDIO → QUIZ DE LECCIÓN → EXAMEN FINAL → RESULTADO

1. [Estudiante] Estudia video + material PDF de cada lección
2. [Estudiante] Presenta quiz de 5 preguntas por lección (≥75% para aprobar)
3. [Estudiante] Cuando todas las lecciones tienen quiz aprobado, presenta examen final de 10 preguntas
4. [Sistema] Calcula nota: 40% quices + 60% examen
5a. [Sistema] Si ≥75%: materia APROBADA → avanza al siguiente módulo
5b. [Sistema] Si <75%: materia REPROBADA → bloquea nuevos intentos
6b. [Estudiante] Solicita habilitación de reintento y paga en Tesorería
7b. [Dir_Ops/Admin] Autoriza reintento en Académico > Habilitaciones
8b. [Estudiante] Puede presentar el examen nuevamente
```

### 12.4 Flujo de Mantenimiento de Aeronave

```
DETECCIÓN → BLOQUEO → MANTENIMIENTO → LIBERACIÓN

1. [Mantenimiento] Detecta necesidad de mantenimiento
2. [Mantenimiento] Cambiar estado aeronave a "mantenimiento" → bloqueada para nuevas reservas
3. [Mantenimiento] Registrar el mantenimiento realizado (tipo, horas, técnico)
4. Si aplica: cerrar ítems MEL relacionados
5. Si aplica: marcar ADs como completadas
6. [Mantenimiento/Dir_Ops] Cambiar estado aeronave a "disponible"
7. [Sistema] La aeronave vuelve a estar disponible para reservas
```

---

## 13. ALERTAS Y VENCIMIENTOS

El sistema monitorea automáticamente **5 tipos de vencimientos** críticos:

| Tipo                          | Modelo                           | Acción cuando vence                      |
| ----------------------------- | -------------------------------- | ---------------------------------------- |
| Certificado Médico Estudiante | CertMedico                       | Bloquea nuevas reservas de vuelo         |
| Licencia de Instructor        | Instructor.venc_licencia         | Bloquea al instructor en nuevas reservas |
| Certificado Aeronavegabilidad | Aeronave.venc_airworthiness      | Bloquea la aeronave para vuelos          |
| Seguro de Aeronave            | Aeronave.venc_seguro             | Bloquea la aeronave para vuelos          |
| Certificaciones de Instructor | CertInstructor.fecha_vencimiento | Alerta en dashboard                      |

### Niveles de Alerta

| Nivel          | Color    | Condición                    |
| -------------- | -------- | ---------------------------- |
| 🔴 VENCIDO     | Rojo     | Fecha de vencimiento ya pasó |
| 🟠 CRÍTICO     | Naranja  | Vence en menos de 15 días    |
| 🟡 ADVERTENCIA | Amarillo | Vence en 15-90 días          |
| 🟢 VIGENTE     | Verde    | Vence en más de 90 días      |

### Ver Alertas

1. Ir a **Vencimientos** (en el menú principal) o clic en el icono de campana 🔔
2. El **"Radar de Alertas"** muestra todos los ítems críticos
3. Filtrar por tipo: aeronave / estudiante / instructor / documento
4. Clic en **"Sincronizar"** para recalcular todos los vencimientos con la fecha actual

---

## 14. RESOLUCIÓN DE PROBLEMAS COMUNES

### ❌ "No se puede crear la reserva — Aeronave no disponible"

**Causa:** La aeronave está en estado `mantenimiento` o tiene aeronavegabilidad vencida.
**Solución:** Verificar en **Aeronaves** el estado y fechas de vencimiento. Si está en mantenimiento, esperar a que sea liberada.

### ❌ "No se puede crear la reserva — Estudiante sin médico vigente"

**Causa:** El certificado médico del estudiante está vencido o no registrado.
**Solución:** Ir a **Estudiantes > Expediente > Certificados Médicos** y registrar el nuevo certificado con la fecha de vencimiento correcta.

### ❌ "No se puede crear la reserva — Instructor sin licencia vigente"

**Causa:** La licencia del instructor está vencida en el sistema.
**Solución:** Ir a **Instructores > Detalle** y actualizar la fecha de vencimiento de la licencia.

### ❌ "El estudiante no puede acceder al Aula Virtual"

**Causa:** El usuario no tiene rol `estudiante` o no está vinculado a un programa.
**Solución:** Verificar en **Seguridad > Usuarios** que el rol sea `estudiante`. Verificar en **Estudiantes** que tenga un programa asignado y etapa activa.

### ❌ "El examen final no aparece / está bloqueado"

**Causa 1:** El estudiante ya tiene un intento fallido y no tiene habilitación de reintento.
**Causa 2:** Las preguntas del banco no están cargadas para esa materia.
**Solución 1:** Ir a **Académico > Habilitaciones** y autorizar el reintento.
**Solución 2:** Ir a **Académico > Gestión Materias** y cargar preguntas al banco de la materia.

### ❌ "El botón UNIRSE al Aula Virtual redirige al Dashboard"

**Causa:** El campo `link_meet` de la materia no tiene el protocolo `https://`.
**Solución:** Ir a **Académico > Gestión Materias**, editar la materia y asegurarse que el link de Meet empiece por `https://meet.google.com/...`

### ❌ "La reserva fue cancelada automáticamente"

**Causa:** El estudiante no confirmó el plan de vuelo dentro de las 24 horas.
**Solución:** El instructor debe crear una nueva reserva. Recordar al estudiante que debe revisar **Cronograma** y confirmar dentro del plazo.

### ❌ "El PDF de la lección no se ve en el Aula Virtual"

**Causa:** El documento no tiene una URL válida o el visor de Google no puede acceder al archivo.
**Solución:** Verificar que la URL del documento en **Gestión Materias > Lección** sea pública y accesible. Para archivos de Google Drive, asegurarse de que el permiso sea "Cualquier persona con el enlace".

### ❌ "Error 401 — Token expirado"

**Causa:** La sesión del usuario expiró.
**Solución:** El sistema redirige automáticamente al login. Ingresar nuevamente con las credenciales.

### ❌ "Error 403 — Sin permiso"

**Causa:** El rol del usuario no tiene acceso al recurso solicitado.
**Solución:** Verificar con el administrador que el rol asignado sea el correcto.

---

## APÉNDICE A — ATAJOS DE TECLADO

| Atajo       | Función                            |
| ----------- | ---------------------------------- |
| `ESC`       | Cerrar diálogos / modales          |
| Flechas ← → | Navegar entre preguntas del examen |

---

## APÉNDICE B — REFERENCIAS REGULATORIAS

| Norma       | Aplica a                                                      |
| ----------- | ------------------------------------------------------------- |
| RAC 61      | Requisitos de horas mínimas de vuelo para licencias de piloto |
| RAC 65      | Licencias de instructores y técnicos de mantenimiento         |
| RAC 67      | Certificados médicos aeronáuticos para pilotos                |
| RAC 91      | Reglas generales de vuelo y operación de aeronaves            |
| RAC 43      | Mantenimiento, revisiones y reparaciones de aeronaves         |
| RAC 141     | Requisitos para escuelas de entrenamiento aeronáutico         |
| Anexos OACI | Estándares internacionales aplicables                         |

---

## APÉNDICE C — CONTACTO Y SOPORTE

Para soporte técnico del sistema, reportar problemas en el repositorio del proyecto o contactar al administrador del sistema asignado a la escuela.

---

_Manual generado automáticamente basado en el código fuente del sistema — School of Aviation Training V2 · RAC 141 Colombia_
