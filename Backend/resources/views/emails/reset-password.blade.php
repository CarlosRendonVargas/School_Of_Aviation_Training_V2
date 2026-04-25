<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Restablecer contraseña</title>
<style>
  body { margin:0; padding:0; background:#0f1721; font-family:'Segoe UI',Arial,sans-serif; }
  .wrapper { max-width:600px; margin:40px auto; background:#111827; border-radius:16px; overflow:hidden; border:1px solid rgba(255,255,255,0.06); }
  .header { background:linear-gradient(135deg,#A10B13,#7f0a10); padding:32px 40px; text-align:center; }
  .header h1 { color:#fff; margin:0; font-size:22px; letter-spacing:1px; text-transform:uppercase; }
  .header p { color:rgba(255,255,255,0.7); margin:6px 0 0; font-size:12px; letter-spacing:2px; text-transform:uppercase; }
  .body { padding:40px; color:#cbd5e1; line-height:1.7; }
  .body h2 { color:#fff; font-size:20px; margin:0 0 16px; }
  .body p { margin:0 0 16px; font-size:15px; }
  .btn-wrap { text-align:center; margin:32px 0; }
  .btn { display:inline-block; background:linear-gradient(135deg,#A10B13,#c01018); color:#fff !important; text-decoration:none; padding:14px 36px; border-radius:8px; font-size:15px; font-weight:700; letter-spacing:0.5px; }
  .token-box { background:#1e293b; border:1px solid rgba(255,255,255,0.08); border-radius:8px; padding:16px 24px; font-family:monospace; font-size:13px; color:#94a3b8; word-break:break-all; margin:16px 0; }
  .warning { background:rgba(161,11,19,0.1); border-left:3px solid #A10B13; padding:12px 16px; border-radius:0 6px 6px 0; font-size:13px; color:#f87171; margin:24px 0; }
  .footer { background:#0d1117; padding:24px 40px; text-align:center; color:#475569; font-size:12px; }
  .divider { height:1px; background:rgba(255,255,255,0.06); margin:24px 0; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>✈ School of Aviation Training</h1>
    <p>RAC 141 Colombia · Sistema de Instrucción Aeronáutica</p>
  </div>
  <div class="body">
    <h2>Hola, {{ $nombre }}</h2>
    <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en el sistema de gestión aeronáutica.</p>
    <p>Haz clic en el botón para crear una nueva contraseña:</p>
    <div class="btn-wrap">
      <a href="{{ $resetUrl }}" class="btn">Restablecer mi contraseña</a>
    </div>
    <p>Si el botón no funciona, copia este enlace en tu navegador:</p>
    <div class="token-box">{{ $resetUrl }}</div>
    <div class="divider"></div>
    <div class="warning">
      ⏱ Este enlace expirará en <strong>{{ $expira }} minutos</strong>.<br>
      Si no solicitaste este cambio, ignora este correo.
    </div>
    <p style="font-size:13px;color:#64748b;">Por seguridad, nunca compartas este enlace. Si tienes problemas contacta al administrador.</p>
  </div>
  <div class="footer">
    <p>© {{ date('Y') }} School of Aviation Training — RAC 141 Colombia</p>
    <p>Correo automático, no respondas a este mensaje.</p>
  </div>
</div>
</body>
</html>
