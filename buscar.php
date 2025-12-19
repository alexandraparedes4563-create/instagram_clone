<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit(); }
$curr_user = $_SESSION['user'];

$search_results = null;
if (isset($_GET['q'])) {
    $q = $conn->real_escape_string($_GET['q']);
    $search_results = $conn->query("SELECT username, profile_pic FROM users WHERE username LIKE '%$q%' AND username != '$curr_user' LIMIT 10");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Usuarios</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background:#fafafa; font-family:sans-serif; margin:0;">
    <nav style="background:white; border-bottom:1px solid #dbdbdb; padding:10px 20px; display:flex; justify-content:space-between; align-items:center;">
        <h2 style="font-style:italic; margin:0;"><a href="index.php" style="text-decoration:none; color:black;">Instagram</a></h2>
        <a href="index.php" style="text-decoration:none; color:#0095f6; font-weight:bold;">Inicio</a>
    </nav>

    <div style="max-width:500px; margin:30px auto; background:white; border:1px solid #dbdbdb; padding:20px; border-radius:3px;">
        <form method="GET" style="display:flex; gap:10px;">
            <input type="text" name="q" placeholder="Buscar usuario..." required style="flex:1; padding:8px; border:1px solid #dbdbdb; border-radius:3px;">
            <button type="submit" style="background:#0095f6; color:white; border:none; padding:8px 15px; border-radius:4px; font-weight:bold; cursor:pointer;">Buscar</button>
        </form>

        <div style="margin-top:20px;">
            <?php if($search_results): ?>
                <?php while($u = $search_results->fetch_assoc()): ?>
                    <a href="perfil.php?u=<?php echo $u['username']; ?>" style="display:flex; align-items:center; gap:12px; padding:10px; text-decoration:none; color:black; border-bottom:1px solid #fafafa;">
                        <img src="<?php echo $u['profile_pic']; ?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                        <span style="font-weight:bold;">@<?php echo $u['username']; ?></span>
                    </a>
                <?php endwhile; ?>
                <?php if($search_results->num_rows == 0) echo "<p style='color:#8e8e8e; text-align:center;'>No se encontraron usuarios.</p>"; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>