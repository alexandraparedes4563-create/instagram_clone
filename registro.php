<?php
session_start();
include 'db.php';

if (isset($_POST['register'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $default_pic = "https://cdn-icons-png.flaticon.com/512/149/149071.png";

    // Verificar si el usuario ya existe
    $check = $conn->query("SELECT id FROM users WHERE username='$user'");
    if ($check->num_rows == 0) {
        $sql = "INSERT INTO users (username, password, profile_pic) VALUES ('$user', '$pass', '$default_pic')";
        if ($conn->query($sql)) {
            header("Location: login.php?msg=Cuenta creada con éxito");
            exit();
        }
    } else {
        $error = "El nombre de usuario ya está en uso.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regístrate • Instagram</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <div class="auth-box">
            <h1 class="logo" style="font-size: 40px; margin-bottom: 20px;">Instagram</h1>
            <p style="color: #8e8e8e; font-weight: 600; margin-bottom: 20px;">Regístrate para ver fotos y videos de tus amigos.</p>
            
            <?php if(isset($error)) echo "<p style='color: #ed4956; font-size: 14px; margin-bottom: 10px;'>$error</p>"; ?>

            <form method="POST">
                <input type="text" name="username" placeholder="Nombre de usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="register" class="btn-blue" style="margin-top: 10px;">Registrarte</button>
            </form>
            
            <p style="font-size: 12px; color: #8e8e8e; margin-top: 20px;">
                Al registrarte, aceptas nuestras Condiciones, la Política de privacidad y la Política de cookies.
            </p>
        </div>

        <div class="auth-box" style="margin-top: 15px; padding: 20px;">
            <p style="font-size: 14px;">¿Tienes una cuenta? <a href="login.php" style="color: #0095f6; font-weight: 600;">Entrar</a></p>
        </div>
    </main>
</body>
</html>