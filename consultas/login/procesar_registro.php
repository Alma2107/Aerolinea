<?php
session_start();
require_once '../../config/conexion.php';

// Guardamos el modo actual (login o registro) para regresar al mismo estado visual
$modo_actual = isset($_POST['accion_login']) ? 'login' : 'register';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CASO A: REGISTRO DE USUARIOS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    if (isset($_POST['accion_registro'])) {
        $nombre   = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $pw       = $_POST['pw'] ?? '';
        $pw2      = $_POST['pw2'] ?? '';

        if (empty($nombre) || empty($apellido) || empty($email) || empty($pw)) {
            $_SESSION['error'] = "Completá todos los campos obligatorios.";
            header("Location: login.php?mode=$modo_actual");
            exit;
        }
        if ($pw !== $pw2) {
            $_SESSION['error'] = "Las contraseñas no coinciden.";
            header("Location: login.php?mode=$modo_actual");
            exit;
        }

        $password_hash = password_hash($pw, PASSWORD_DEFAULT);
        $estado_cuenta = 1;

        try {
            $sql = "INSERT INTO clientes (nombre, apellido, email, telefono, password_hash, estado_cuenta) 
                    VALUES (:nombre, :apellido, :email, :telefono, :password_hash, :estado_cuenta)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre'        => $nombre,
                'apellido'      => $apellido,
                'email'         => $email,
                'telefono'      => $telefono,
                'password_hash' => $password_hash,
                'estado_cuenta' => $estado_cuenta
            ]);

            $_SESSION['usuario_id']       = $pdo->lastInsertId();
            $_SESSION['usuario_nombre']   = $nombre;
            $_SESSION['usuario_apellido'] = $apellido;

            $redirectAfterAuth = $_SESSION['redirect_after_auth'] ?? '../../index.php';
            unset($_SESSION['redirect_after_auth']);
            header("Location: $redirectAfterAuth");
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['error'] = "Este correo electrónico ya se encuentra registrado.";
            } else {
                $_SESSION['error'] = "Error inesperado al registrar la cuenta.";
            }
            header("Location: login.php?mode=$modo_actual");
            exit;
        }
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CASO B: INICIO DE SESIÓN
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    if (isset($_POST['accion_login'])) {
        $email = trim($_POST['email'] ?? '');
        $pw    = $_POST['pw'] ?? '';

        if (empty($email) || empty($pw)) {
            $_SESSION['error'] = "Ingresá tu email y contraseña.";
            header("Location: login.php?mode=$modo_actual");
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = :email AND estado_cuenta = 1 LIMIT 1");
            $stmt->execute(['email' => $email]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cliente && password_verify($pw, $cliente['password_hash'])) {
                $_SESSION['usuario_id']       = $cliente['id_cliente'];
                $_SESSION['usuario_nombre']   = $cliente['nombre'];
                $_SESSION['usuario_apellido'] = $cliente['apellido'];

                $redirectAfterAuth = $_SESSION['redirect_after_auth'] ?? '../../index.php';
                unset($_SESSION['redirect_after_auth']);
                header("Location: $redirectAfterAuth");
                exit;
            } else {
                $_SESSION['error'] = "El correo o la contraseña son incorrectos.";
                header("Location: login.php?mode=$modo_actual");
                exit;
            }

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error de conexión con el servidor.";
            header("Location: login.php?mode=$modo_actual");
            exit;
        }
    }

} else {
    header("Location: login.php");
    exit;
}