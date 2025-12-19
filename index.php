<?php
session_start();
include 'db.php';
$curr_user = $_SESSION['user'] ?? null;

if (!$curr_user) { header("Location: login.php"); exit(); }

if (isset($_GET['theme'])) { 
    $_SESSION['dark_mode'] = $_GET['theme']; 
    header("Location: index.php"); exit(); 
}
$is_dark = (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == '1');

// CONSULTA PUNTO ROJO
$check_notis = $conn->query("SELECT id FROM notifications WHERE user_to = '$curr_user' AND is_read = 0");
$has_new_notis = ($check_notis && $check_notis->num_rows > 0);

$sugerencias = $conn->query("SELECT username FROM users WHERE username != '$curr_user' ORDER BY RAND() LIMIT 5");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. SUBIR POST
    if (isset($_POST['upload_post']) && !empty($_FILES["image_file"]["name"])) {
        $target = "uploads/p_" . time() . "_" . basename($_FILES["image_file"]["name"]);
        if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target)) {
            $cap = $conn->real_escape_string($_POST['caption']);
            $conn->query("INSERT INTO posts (username, image_url, caption) VALUES ('$curr_user', '$target', '$cap')");
        }
    }
    // 2. SUBIR HISTORIA
    if (isset($_POST['upload_story']) && !empty($_FILES["story_file"]["name"])) {
        $target = "uploads/s_" . time() . "_" . basename($_FILES["story_file"]["name"]);
        if (move_uploaded_file($_FILES["story_file"]["tmp_name"], $target)) {
            $conn->query("INSERT INTO stories (username, image_url) VALUES ('$curr_user', '$target')");
        }
    }
    // 3. ELIMINAR HISTORIA
    if (isset($_POST['delete_story'])) {
        $sid = intval($_POST['story_id']);
        $conn->query("DELETE FROM stories WHERE id=$sid AND username='$curr_user'");
        header("Location: index.php"); exit();
    }
    // 4. LIKES + NOTIFICACION
    if (isset($_POST['like_action'])) {
        $pid = intval($_POST['post_id']);
        $post_info = $conn->query("SELECT username FROM posts WHERE id=$pid")->fetch_assoc();
        if ($post_info) {
            $owner = $post_info['username'];
            $check = $conn->query("SELECT id FROM likes WHERE post_id=$pid AND username='$curr_user'");
            if ($check->num_rows == 0) {
                $conn->query("INSERT INTO likes (post_id, username) VALUES ($pid, '$curr_user')");
                if($owner != $curr_user) {
                    $conn->query("INSERT INTO notifications (user_to, user_from, type, post_id) VALUES ('$owner', '$curr_user', 'like', $pid)");
                }
            } else { $conn->query("DELETE FROM likes WHERE post_id=$pid AND username='$curr_user'"); }
        }
    }
    // 5. COMENTARIOS + NOTIFICACION
    if (isset($_POST['comment_action'])) {
        $pid = intval($_POST['post_id']);
        $txt = $conn->real_escape_string($_POST['comment_text']);
        $post_info = $conn->query("SELECT username FROM posts WHERE id=$pid")->fetch_assoc();
        if ($post_info && !empty($txt)) {
            $owner = $post_info['username'];
            $conn->query("INSERT INTO comments (post_id, username, comment) VALUES ($pid, '$curr_user', '$txt')");
            if($owner != $curr_user) {
                $conn->query("INSERT INTO notifications (user_to, user_from, type, post_id) VALUES ('$owner', '$curr_user', 'comment', $pid)");
            }
        }
    }
    header("Location: index.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram • Clon</title>
    <style>
        :root {
            --bg: <?php echo $is_dark ? '#121212' : '#fafafa'; ?>;
            --card: <?php echo $is_dark ? '#262626' : '#ffffff'; ?>;
            --text: <?php echo $is_dark ? '#ffffff' : '#262626'; ?>;
            --border: <?php echo $is_dark ? '#333' : '#dbdbdb'; ?>;
            --accent: #0095f6;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: var(--bg); color: var(--text); font-family: -apple-system, sans-serif; }
        nav { background: var(--card); border-bottom: 1px solid var(--border); padding: 10px 20px; display: flex; justify-content: center; position: sticky; top: 0; z-index: 1000; }
        .nav-container { width: 100%; max-width: 975px; display: flex; justify-content: space-between; align-items: center; }
        .nav-icons { display: flex; gap: 18px; align-items: center; }
        .nav-icons a { text-decoration: none; color: var(--text); font-size: 20px; position: relative; }
        .noti-dot { width: 8px; height: 8px; background: #ed4956; border-radius: 50%; position: absolute; top: -2px; right: -2px; display: <?php echo $has_new_notis ? 'block' : 'none'; ?>; }
        .main-wrapper { display: flex; justify-content: center; max-width: 850px; margin: 25px auto; gap: 30px; padding: 0 10px; }
        .feed-column { max-width: 470px; width: 100%; }
        .stories-bar { display: flex; gap: 15px; overflow-x: auto; background: var(--card); border: 1px solid var(--border); padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .story-circle { width: 58px; height: 58px; border-radius: 50%; border: 2px solid #ff8501; padding: 2px; object-fit: cover; }
        .post { background: var(--card); border: 1px solid var(--border); margin-bottom: 24px; border-radius: 8px; overflow: hidden; }
        .post-img { width: 100%; display: block; }
        .comment-form { border-top: 1px solid var(--border); padding: 10px; display: flex; gap: 10px; }
        .sidebar { width: 320px; position: sticky; top: 85px; }
        #storyViewer { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.98); z-index: 4000; justify-content: center; align-items: center; flex-direction: column; }
    </style>
</head>
<body>

<nav>
    <div class="nav-container">
        <h2 onclick="location.href='index.php'" style="font-style:italic; cursor:pointer;">Instagram</h2>
        <div class="nav-icons">
            <a href="?theme=<?php echo $is_dark ? '0' : '1'; ?>">🌓</a>
            <a href="notificaciones.php">❤️ <div class="noti-dot"></div></a>
            <a href="perfil.php?u=<?php echo $curr_user; ?>">👤</a>
            <a href="logout.php" style="font-size: 12px; color: #ed4956; font-weight: bold;">Salir</a>
        </div>
    </div>
</nav>

<div class="main-wrapper">
    <div class="feed-column">
        <div class="post" style="padding: 15px;">
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="image_file" required style="margin-bottom:10px;">
                <input type="text" name="caption" placeholder="Escribe algo..." style="width:100%; padding:8px; margin-bottom:10px; background:var(--bg); border:1px solid var(--border); color:var(--text);">
                <button type="submit" name="upload_post" style="width:100%; background:var(--accent); color:white; border:none; padding:8px; border-radius:4px; font-weight:bold;">Publicar</button>
            </form>
        </div>

        <div class="stories-bar">
            <div style="text-align:center; min-width:65px; cursor:pointer;">
                <form method="POST" enctype="multipart/form-data" id="fStory">
                    <label style="cursor:pointer;">
                        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" class="story-circle" style="border-color:var(--border);">
                        <input type="file" name="story_file" hidden onchange="document.getElementById('fStory').submit()">
                        <input type="hidden" name="upload_story">
                    </label>
                </form>
            </div>
            <?php
            $stories = $conn->query("SELECT * FROM stories ORDER BY created_at DESC LIMIT 10");
            while($s = $stories->fetch_assoc()): ?>
                <div onclick="openStory('<?php echo $s['image_url']; ?>', '<?php echo $s['username']; ?>', '<?php echo $s['id']; ?>')" style="text-align:center; min-width:65px; cursor:pointer;">
                    <img src="<?php echo $s['image_url']; ?>" class="story-circle">
                    <div style="font-size:10px;"><?php echo $s['username']; ?></div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php
        $feed = $conn->query("SELECT * FROM posts ORDER BY RAND() LIMIT 15");
        while($p = $feed->fetch_assoc()): 
            $pid = $p['id'];
            $likes = $conn->query("SELECT id FROM likes WHERE post_id=$pid")->num_rows;
        ?>
        <div class="post">
            <div style="padding:12px; font-weight:bold;">@<?php echo $p['username']; ?></div>
            <img src="<?php echo $p['image_url']; ?>" class="post-img">
            <div style="padding:12px;">
                <form method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $pid; ?>">
                    <button type="submit" name="like_action" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--text);">❤️</button>
                </form>
                <div style="font-size:14px; margin-top:5px;"><b><?php echo $likes; ?> likes</b></div>
                <div style="font-size:14px;"><b><?php echo $p['username']; ?></b> <?php echo htmlspecialchars($p['caption']); ?></div>
            </div>
            <form method="POST" class="comment-form">
                <input type="hidden" name="post_id" value="<?php echo $pid; ?>">
                <input type="text" name="comment_text" placeholder="Comentar..." style="flex:1; border:none; background:none; color:var(--text); outline:none;">
                <button type="submit" name="comment_action" style="background:none; border:none; color:var(--accent); font-weight:bold;">Enviar</button>
            </form>
        </div>
        <?php endwhile; ?>
    </div>

    <aside class="sidebar">
        <div style="font-weight:bold; color:gray; margin-bottom:15px;">Sugerencias</div>
        <?php while($sug = $sugerencias->fetch_assoc()): ?>
            <div style="display:flex; justify-content:space-between; margin-bottom:10px; align-items:center;">
                <div style="font-weight:bold; font-size:14px;">@<?php echo $sug['username']; ?></div>
                <a href="perfil.php?u=<?php echo $sug['username']; ?>" style="color:var(--accent); text-decoration:none; font-size:12px; font-weight:bold;">Ver</a>
            </div>
        <?php endwhile; ?>
    </aside>
</div>

<div id="storyViewer">
    <span onclick="closeStory()" style="position:absolute; top:20px; right:30px; color:white; font-size:40px; cursor:pointer;">&times;</span>
    <div id="stHeader" style="color:white; font-weight:bold; margin-bottom:10px;"></div>
    <img id="stImg" src="" style="max-width:90%; max-height:70vh; border-radius:8px;">
    <form method="POST" style="margin-top:20px;">
        <input type="hidden" name="story_id" id="sidInp">
        <button type="submit" name="delete_story" id="btnDel" style="background:#ed4956; color:white; border:none; padding:10px 20px; border-radius:5px; font-weight:bold; display:none;">Eliminar</button>
    </form>
</div>

<script>
    const sessionUser = "<?php echo $curr_user; ?>";
    function openStory(url, owner, id) {
        document.getElementById('storyViewer').style.display = 'flex';
        document.getElementById('stImg').src = url;
        document.getElementById('stHeader').innerText = '@' + owner;
        const btn = document.getElementById('btnDel');
        if(owner.trim() === sessionUser.trim()) {
            btn.style.display = 'block';
            document.getElementById('sidInp').value = id;
        } else { btn.style.display = 'none'; }
    }
    function closeStory() { document.getElementById('storyViewer').style.display = 'none'; }
</script>

</body>
</html>