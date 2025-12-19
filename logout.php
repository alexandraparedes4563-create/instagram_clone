<?php
session_start();
// Destruye todas las variables de sesión
session_unset();
// Destruye la sesión físicamente en el servidor
session_destroy();
// Redirige al usuario a la página de inicio o login
header("Location: index.php");
exit();
?>