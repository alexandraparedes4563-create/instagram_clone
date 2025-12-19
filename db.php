<?php
$servername = "localhost";
$username   = "root"; 
$password   = "";     
$dbname     = "instagram_clone"; // Nombre con el guion bajo como me confirmaste

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión local: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>