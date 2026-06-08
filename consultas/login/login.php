<?php
require_once '../../config/conexion.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        if ((int)$usuario['estado_cuenta'] === 0) {
            $error = "Esta cuenta fue eliminada.";
        } else {
            $_SESSION['usuario_id'] = $usuario['id_cliente'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            header("Location: ../../index.php");
            exit();
        }
    } else {
        $error = "Credenciales incorrectas.";
    }
}

include_once '../../includes/header.php';
?>
<div style="max-width: 450px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px;">
    <h2>Iniciar Sesión</h2>
    <?php if($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <form action="login.php" method="POST" style="display:flex; flex-direction:column; gap:10px; margin-top:15px;">
        <input type="email" name="email" placeholder="Correo" required style="padding:10px;">
        <input type="password" name="password" placeholder="Contraseña" required style="padding:10px;">
        <button type="submit" class="btn-next">Ingresar</button>
    </form>
</div>
</body>
</html>