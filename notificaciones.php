<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit(); }
$curr_user = $_SESSION['user'];

$is_dark = (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == '1');

// Marcar como leídas
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_to = '$curr_user'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones • Instagram</title>
    <style>
        body { 
            background: <?php echo $is_dark ? '#121212' : '#fafafa'; ?>; 
            color: <?php echo $is_dark ? '#fff' : '#262626'; ?>;
            font-family: -apple-system, sans-serif; margin: 0; 
        }
        .container { max-width: 600px; margin: 20px auto; background: <?php echo $is_dark ? '#262626' : '#fff'; ?>; border: 1px solid <?php echo $is_dark ? '#333' : '#dbdbdb'; ?>; border-radius: 8px; }
        .header { padding: 15px; border-bottom: 1px solid <?php echo $is_dark ? '#333' : '#dbdbdb'; ?>; font-weight: bold; font-size: 18px; }
        .noti-item { padding: 15px; border-bottom: 1px solid <?php echo $is_dark ? '#333' : '#fafafa'; ?>; display: flex; align-items: center; justify-content: space-between; }
        .user-link { font-weight: bold; text-decoration: none; color: <?php echo $is_dark ? '#fff' : '#262626'; ?>; }
        .time { color: #8e8e8e; font-size: 12px; margin-left: 10px; }
        .btn-back { text-decoration: none; color: #0095f6; font-weight: bold; }
    </style>
</head>
<body>
    <nav style="padding: 15px 20px; border-bottom: 1px solid <?php echo $is_dark ? '#333' : '#dbdbdb'; ?>; display: flex; justify-content: space-between; background: <?php echo $is_dark ? '#262626' : '#fff'; ?>;">
        <a href="index.php" style="text-decoration:none; color:inherit; font-weight:bold; font-style:italic; font-size:20px;">Instagram</a>
        <a href="index.php" class="btn-back">Volver al inicio</a>
    </nav>

    <div class="container">
        <div class="header">Notificaciones</div>
        <?php
        // Traemos las notificaciones y también unimos con la tabla posts para saber qué imagen es (opcional)
        $notifs = $conn->query("SELECT * FROM notifications WHERE user_to = '$curr_user' ORDER BY created_at DESC");
        
        if($notifs && $notifs->num_rows > 0):
            while($n = $notifs->fetch_assoc()): ?>
                <div class="noti-item">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" style="width:35px; border-radius:50%;">
                        <span>
                            <a href="perfil.php?u=<?php echo $n['user_from']; ?>" class="user-link">@<?php echo $n['user_from']; ?></a> 
                            <?php echo ($n['type'] == 'like') ? "le dio me gusta a tu foto." : "comentó tu publicación."; ?>
                            <span class="time"><?php echo date("d M, H:i", strtotime($n['created_at'])); ?></span>
                        </span>
                    </div>
                    <?php 
                    // Si tienes el post_id, podrías mostrar una miniatura del post aquí
                    ?>
                </div>
            <?php endwhile;
        else: ?>
            <div style="padding: 40px; text-align: center; color: #8e8e8e;">
                <div style="font-size: 50px; margin-bottom: 10px;">❤️</div>
                <p>No tienes notificaciones por el momento.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>