<?php
session_start();
include 'db.php';

$curr_user = $_SESSION['user'] ?? null;
$profile_user = $_GET['u'] ?? null;

if (!$curr_user || !$profile_user) {
    header("Location: login.php");
    exit();
}

// LOGICA PARA CAMBIAR FOTO DE PERFIL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_avatar'])) {
    if (!empty($_FILES["avatar"]["name"])) {
        $target = "uploads/av_" . time() . "_" . basename($_FILES["avatar"]["name"]);
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target)) {
            $conn->query("UPDATE users SET profile_pic = '$target' WHERE username = '$curr_user'");
            header("Location: perfil.php?u=$curr_user"); exit();
        }
    }
}

$is_dark = (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == '1');

// Obtener datos del usuario del perfil (incluyendo su foto)
$user_data = $conn->query("SELECT * FROM users WHERE username='$profile_user'")->fetch_assoc();
$avatar = (!empty($user_data['profile_pic'])) ? $user_data['profile_pic'] : "https://cdn-icons-png.flaticon.com/512/149/149071.png";

$posts_count = $conn->query("SELECT id FROM posts WHERE username='$profile_user'")->num_rows;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de @<?php echo htmlspecialchars($profile_user); ?></title>
    <style>
        :root { 
            --bg: <?php echo $is_dark ? '#121212' : '#fafafa'; ?>; 
            --card: <?php echo $is_dark ? '#262626' : '#ffffff'; ?>; 
            --text: <?php echo $is_dark ? '#ffffff' : '#262626'; ?>; 
            --border: <?php echo $is_dark ? '#333' : '#dbdbdb'; ?>; 
            --accent: #0095f6;
        }
        body { background: var(--bg); color: var(--text); font-family: -apple-system, sans-serif; margin: 0; }
        nav { background: var(--card); border-bottom: 1px solid var(--border); padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .container { max-width: 935px; margin: 30px auto; padding: 0 20px; }
        .profile-header { display: flex; align-items: center; margin-bottom: 44px; gap: 40px; }
        
        .profile-pic-container { position: relative; }
        .profile-pic { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border); }
        .edit-overlay { position: absolute; bottom: 5px; right: 5px; background: var(--accent); color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid var(--card); font-size: 18px; }

        .profile-info h2 { font-weight: 300; font-size: 28px; margin: 0; }
        .stats { display: flex; gap: 20px; margin: 20px 0; list-style: none; padding: 0; }
        .photo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
        .photo-item { aspect-ratio: 1 / 1; background: #333; overflow: hidden; }
        .photo-item img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>

<nav>
    <h2 onclick="location.href='index.php'" style="font-style:italic; cursor:pointer;">Instagram</h2>
    <div style="display:flex; gap:20px;">
        <a href="index.php" style="text-decoration:none; color:var(--text); font-size:22px;">🏠</a>
        <a href="notificaciones.php" style="text-decoration:none; color:var(--text); font-size:22px;">❤️</a>
    </div>
</nav>

<div class="container">
    <header class="profile-header">
        <div class="profile-pic-container">
            <img src="<?php echo $avatar; ?>" class="profile-pic">
            <?php if($curr_user == $profile_user): ?>
                <form method="POST" enctype="multipart/form-data" id="avatarForm">
                    <label class="edit-overlay">
                        +
                        <input type="file" name="avatar" hidden onchange="document.getElementById('avatarForm').submit()">
                        <input type="hidden" name="update_avatar">
                    </label>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($profile_user); ?></h2>
            <ul class="stats">
                <li><b><?php echo $posts_count; ?></b> publicaciones</li>
                <li><b>0</b> seguidores</li>
                <li><b>0</b> seguidos</li>
            </ul>
            <div style="font-weight: bold;">Bio de @<?php echo htmlspecialchars($profile_user); ?></div>
            <div style="font-size: 14px; color: gray;">Usuario de Instagram Clone</div>
        </div>
    </header>

    <div class="photo-grid">
        <?php
        $user_posts = $conn->query("SELECT * FROM posts WHERE username='$profile_user' ORDER BY created_at DESC");
        while($img = $user_posts->fetch_assoc()): ?>
            <div class="photo-item">
                <img src="<?php echo $img['image_url']; ?>">
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>