<?php
require_once '../../config/conexion.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = $_POST['password'];

    if (!empty($nombre) && !empty($apellido) && !empty($email) && !empty($password)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        try {
            $stmt = $pdo->prepare("INSERT INTO clientes (nombre, apellido, email, telefono, password_hash, estado_cuenta) VALUES (:nombre, :apellido, :email, :telefono, :password_hash, 1)");
            $stmt->execute([
                'nombre' => $nombre, 'apellido' => $apellido, 'email' => $email, 'telefono' => $telefono, 'password_hash' => $password_hash
            ]);
            $_SESSION['usuario_id'] = $pdo->lastInsertId();
            $_SESSION['usuario_nombre'] = $nombre;
                $redirectAfterAuth = $_SESSION['redirect_after_auth'] ?? '../../index.php';
                unset($_SESSION['redirect_after_auth']);
                header("Location: $redirectAfterAuth");
            exit();
        } catch (\PDOException $e) {
            $error = ($e->getCode() == 23000) ? "El correo ya existe." : "Error: " . $e->getMessage();
        }
    }
}
include_once '../../includes/header.php';
?>
<div style="max-width: 450px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px;">
    <h2>Crear una Cuenta</h2>
    <?php if($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <form action="registro.php" method="POST" style="display:flex; flex-direction:column; gap:10px; margin-top:15px;">
        <input type="text" name="nombre" placeholder="Nombre *" required style="padding:10px;">
        <input type="text" name="apellido" placeholder="Apellido *" required style="padding:10px;">
        <input type="email" name="email" placeholder="Correo *" required style="padding:10px;">
        <input type="text" name="telefono" placeholder="Teléfono" style="padding:10px;">
        <input type="password" name="password" placeholder="Contraseña *" required style="padding:10px;">
        <button type="submit" class="btn-next">Registrarse</button>
    </form>
</div>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>