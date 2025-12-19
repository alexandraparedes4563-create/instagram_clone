<?php
session_start();
include 'db.php';

// Si ya está logueado, mandarlo al inicio
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];

    $res = $conn->query("SELECT * FROM users WHERE username='$user'");
    if ($res->num_rows > 0) {
        $u = $res->fetch_assoc();
        if (password_verify($pass, $u['password'])) {
            $_SESSION['user'] = $u['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "La contraseña es incorrecta.";
        }
    } else {
        $error = "El nombre de usuario no existe.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión • Instagram</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <div class="auth-box">
            <h1 class="logo" style="font-size: 40px; margin-bottom: 30px;">Instagram</h1>
            
            <?php 
            if(isset($error)) echo "<p style='color: #ed4956; font-size: 14px; margin-bottom: 15px;'>$error</p>"; 
            if(isset($_GET['msg'])) echo "<p style='color: #262626; font-size: 14px; margin-bottom: 15px;'>".$_GET['msg']."</p>";
            ?>

            <form method="POST">
                <input type="text" name="username" placeholder="Teléfono, usuario o correo electrónico" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="login" class="btn-blue" style="margin-top: 10px;">Iniciar sesión</button>
            </form>

            <div style="margin: 20px 0; display: flex; align-items: center; color: #8e8e8e;">
                <hr style="flex-grow: 1; border: 0; border-top: 1px solid #dbdbdb;">
                <span style="margin: 0 15px; font-size: 13px; font-weight: 600;">O</span>
                <hr style="flex-grow: 1; border: 0; border-top: 1px solid #dbdbdb;">
            </div>

            <p style="color: #385185; font-size: 14px; font-weight: 600; cursor: pointer;">Iniciar sesión con Facebook</p>
            <p style="font-size: 12px; margin-top: 15px; cursor: pointer;">¿Olvidaste tu contraseña?</p>
        </div>

        <div class="auth-box" style="margin-top: 15px; padding: 20px;">
            <p style="font-size: 14px;">¿No tienes una cuenta? <a href="registro.php" style="color: #0095f6; font-weight: 600;">Regístrate</a></p>
        </div>
    </main>
</body>
</html>