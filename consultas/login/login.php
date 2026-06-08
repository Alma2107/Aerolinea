<?php
session_start();
// Si el usuario ya está logueado, lo mandamos directo al index principal
if (isset($_SESSION['usuario_nombre'])) {
    header("Location: ../../index.php");
    exit;
}

// Capturamos el error guardado y limpiamos la sesión inmediatamente
$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);

$mode = isset($_GET['mode']) && $_GET['mode'] === 'login' ? 'login' : 'register';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>JetAway - Ingresar</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,600;0,700;0,900;1,900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{width:100%;height:100%;overflow:hidden;font-family:'Inter',sans-serif;background:#f3f4f6}
        
        #stage{
            position:fixed;
            inset:0;
            overflow:hidden;
            display: flex;
            width: 100vw;
            height: 100vh;
        }
        
        #sky-panel {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 50%;
            left: 50%;
            background-image: url('../../img/imagenlogin.jfif');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 2;
            transition: left 1.2s cubic-bezier(.86,0,.07,1);
        }
        
        body.is-login #sky-panel {
            left: 0;
        }
        
        #white-panel{
            position:absolute;
            top:0;
            bottom:0;
            width:50%;
            background:#fff;
            left:0;
            border-radius:0 48px 48px 0;
            box-shadow:12px 0 60px rgba(0,40,100,.08);
            transition:left 1.2s cubic-bezier(.86,0,.07,1),
                       border-radius 1.2s cubic-bezier(.86,0,.07,1);
            display:flex;
            flex-direction:column;
            justify-content:center;
            padding:0 8%;
            overflow:hidden;
            z-index:5;
        }
        
        body.is-login #white-panel {
            left:50%;
            border-radius:48px 0 0 48px;
            box-shadow:-12px 0 60px rgba(0,40,100,.08);
        }
        
        .form-inner{position:relative;z-index:6; width: 100%;}
        
        .eyebrow{
            font-family:'Montserrat',sans-serif;
            font-size:10px;font-weight:700;
            letter-spacing:3.5px;text-transform:uppercase;
            color:#7fa8d0;margin-bottom:8px;
        }
        
        .main-title{
            font-family:'Montserrat',sans-serif;
            font-weight:900;font-size:32px;line-height:1.1;
            color:#0a1f3d;margin-bottom:16px; /* Ajustado el margen inferior */
            white-space: nowrap;
        }

        /* NUEVO: Clase de estilo para las alertas de error integradas */
        .error-msg {
            font-size: 12px;
            font-weight: 600;
            color: #dc2626;
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .form-stack{position:relative; width: 100%;}
        .fv{
            width: 100%;
            transition:transform .9s cubic-bezier(.86,0,.07,1),
                       opacity .9s cubic-bezier(.86,0,.07,1);
        }
        
        #rv{transform:translateX(0);opacity:1;position:relative;}
        #lv{transform:translateX(110%);opacity:0;position:absolute;top:0;left:0;}
        
        body.is-login #rv{transform:translateX(-110%);opacity:0;position:absolute;top:0;left:0;}
        body.is-login #lv{transform:translateX(0);opacity:1;position:relative;}
        
        .r2{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;}
        .fg{display:flex;flex-direction:column;margin-bottom:12px;}
        .fg label{
            font-size:10px;font-weight:700;letter-spacing:.8px;
            text-transform:uppercase;color:#4a7ab0;margin-bottom:5px;
        }
        .fg input{
            border:1.5px solid #dde8f5;border-radius:10px;
            padding:11px 14px;font-size:13.5px;
            color:#0a1f3d;background:#f6f9fd;outline:none;
            font-family:'Inter',sans-serif;
            transition:border-color .2s,box-shadow .2s,background .2s;
            width: 100%;
        }
        .fg input:focus{
            border-color:#1a8fe3;
            box-shadow:0 0 0 3.5px rgba(26,143,227,.13);
            background:#fff;
        }
        
        .btn{
            width:100%;padding:14px;border:none;border-radius:11px;
            background:linear-gradient(135deg,#0d7fd4 0%,#1ab4f5 100%);
            color:#fff;font-family:'Montserrat',sans-serif;
            font-weight:700;font-size:14.5px;letter-spacing:.5px;
            cursor:pointer;margin-top:4px;
            box-shadow:0 6px 24px rgba(13,127,212,.38);
            transition:transform .15s,box-shadow .15s,filter .15s;
        }
        .btn:hover{
            transform:translateY(-2px);
            box-shadow:0 10px 32px rgba(13,127,212,.45);
            filter:brightness(1.05);
        }
        
        .switch{
            text-align:center;margin-top:18px;
            font-size:13px;color:#8fa8bf;
        }
        .switch a{
            color:#0d7fd4;font-weight:600;
            cursor:pointer;text-decoration:none;
        }
        .switch a:hover{text-decoration:underline;}
        
        .dots{display:flex;gap:7px;justify-content:center;margin-top:13px;}
        .dot{
            width:8px;height:8px;border-radius:50%;
            background:#d5e5f5;
            transition:background .35s,width .35s;
        }
        .dot.on{background:#1a8fe3;width:22px;border-radius:4px;}
    </style>
</head>
<body class="<?= $mode==='login'?'is-login':'' ?>">
 
<div id="stage">
 
    <div id="sky-panel"></div>
 
    <div id="white-panel">
        <div class="form-inner">
            <div class="eyebrow" id="eyebrow"><?= $mode==='login'?'bienvenido de vuelta':'comenzá tu viaje' ?></div>
            <div class="main-title" id="main-title"><?= $mode==='login'?'INICIAR SESIÓN':'CREAR CUENTA' ?></div>
            
            <?php if ($error): ?>
                <div class="error-msg" id="error-box">
                    <span>⚠</span> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
 
            <div class="form-stack">
                <div id="rv" class="fv">
                    <form method="POST" action="procesar_registro.php" autocomplete="off">
                        <div class="r2">
                            <div class="fg"><label>Nombre</label><input type="text" name="nombre" placeholder="María" required></div>
                            <div class="fg"><label>Apellido</label><input type="text" name="apellido" placeholder="García" required></div>
                        </div>
                        <div class="fg"><label>Email</label><input type="email" name="email" placeholder="maria@correo.com" required></div>
                        <div class="fg"><label>Teléfono</label><input type="tel" name="telefono" placeholder="+54 11 0000-0000"></div>
                        <div class="r2">
                            <div class="fg"><label>Contraseña</label><input type="password" name="pw" placeholder="••••••••" required></div>
                            <div class="fg"><label>Confirmar</label><input type="password" name="pw2" placeholder="••••••••" required></div>
                        </div>
                        <button type="submit" name="accion_registro" class="btn">Crear mi cuenta →</button>
                    </form>
                    <div class="switch">¿Ya tenés cuenta? <a href="#" id="go-to-login">Iniciá sesión acá</a></div>
                    <div class="dots"><div class="dot on"></div><div class="dot"></div></div>
                </div>
 
                <div id="lv" class="fv">
                    <form method="POST" action="procesar_registro.php" autocomplete="off">
                        <div class="fg" style="margin-bottom:18px"><label>Email</label><input type="email" name="email" placeholder="maria@correo.com" required></div>
                        <div class="fg" style="margin-bottom:24px"><label>Contraseña</label><input type="password" name="pw" placeholder="••••••••" required></div>
                        
                        <button type="submit" name="accion_login" class="btn">Ingresar →</button>
                    </form>
                    <div class="switch">¿No tenés cuenta? <a href="#" id="go-to-register">Registrate gratis</a></div>
                    <div class="dots"><div class="dot"></div><div class="dot on"></div></div>
                </div>
            </div>
        </div>
    </div>
 
</div>
 
<script src="../../js/login/login-animacion.js"></script>
<script>
    // Limpia la caja de error visualmente si el usuario cambia voluntariamente de pestaña
    const cleanError = () => {
        const errBox = document.getElementById('error-box');
        if(errBox) errBox.style.display = 'none';
    };
    document.getElementById('go-to-login')?.addEventListener('click', cleanError);
    document.getElementById('go-to-register')?.addEventListener('click', cleanError);
</script>
</body>
</html>