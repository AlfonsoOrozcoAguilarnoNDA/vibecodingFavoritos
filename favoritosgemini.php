<?php
include 'config.php';

// 1. SEGURIDAD: IP + CONTRASEÑA HARDCODED
$password_hardcoded = "gotham4feb*";
$client_ip = $_SERVER['REMOTE_ADDR'];
$allowed_ips = unserialize(AUTHORIZED_IPS);

// Verificación de acceso
if (!in_array($client_ip, $allowed_ips)) {
    if (isset($_POST['login_pass'])) {
        if ($_POST['login_pass'] === $password_hardcoded) {
            $_SESSION['auth_user'] = true;
        } else {
            $error_auth = "Clave incorrecta.";
        }
    }
    if (!isset($_SESSION['auth_user'])) {
        die('<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><title>Acceso Gotham</title></head><body class="bg-dark text-white d-flex align-items-center vh-100"><div class="container text-center" style="max-width:400px;"><h3><i class="fas fa-lock"></i> IP No Autorizada</h3><p class="small text-secondary">IP: '.$client_ip.'</p><form method="POST"><input type="password" name="login_pass" class="form-control mb-3" placeholder="Ingresa la clave hardcoded" required><button class="btn btn-primary w-100">Entrar</button></form>'.(isset($error_auth)?'<p class="text-danger mt-2">'.$error_auth.'</p>':'').'</div></body></html>');
    }
}

// 2. LÓGICA DE SALIR
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// 3. CRUD (PROCEDURAL)
// --- ALTAS ---
if (isset($_POST['save_cat'])) {
    $name = mysqli_real_escape_string($link, $_POST['category_name']);
    $icon = mysqli_real_escape_string($link, $_POST['category_icon']);
    $color = mysqli_real_escape_string($link, $_POST['category_color']);
    if (isset($_POST['cat_id']) && !empty($_POST['cat_id'])) {
        mysqli_query($link, "UPDATE LINK_CATEGORIES SET category_name='$name', category_icon='$icon', category_color='$color' WHERE category_id=".$_POST['cat_id']);
    } else {
        mysqli_query($link, "INSERT INTO LINK_CATEGORIES (category_name, category_icon, category_color) VALUES ('$name', '$icon', '$color')");
    }
    header("Location: index.php");
}

if (isset($_POST['save_link'])) {
    $cid = $_POST['category_id'];
    $tit = mysqli_real_escape_string($link, $_POST['link_title']);
    $url = mysqli_real_escape_string($link, $_POST['link_url']);
    if (isset($_POST['lid']) && !empty($_POST['lid'])) {
        mysqli_query($link, "UPDATE LINKS SET category_id=$cid, link_title='$tit', link_url='$url' WHERE link_id=".$_POST['lid']);
    } else {
        mysqli_query($link, "INSERT INTO LINKS (category_id, link_title, link_url) VALUES ($cid, '$tit', '$url')");
    }
    header("Location: index.php");
}

// --- BAJAS ---
if (isset($_GET['del_cat'])) { mysqli_query($link, "DELETE FROM LINK_CATEGORIES WHERE category_id=".(int)$_GET['del_cat']); header("Location: index.php"); }
if (isset($_GET['del_link'])) { mysqli_query($link, "DELETE FROM LINKS WHERE link_id=".(int)$_GET['del_link']); header("Location: index.php"); }

// --- DATOS PARA EDICIÓN ---
$edit_cat = null; if (isset($_GET['edit_cat'])) { $res = mysqli_query($link, "SELECT * FROM LINK_CATEGORIES WHERE category_id=".(int)$_GET['edit_cat']); $edit_cat = mysqli_fetch_assoc($res); }
$edit_link = null; if (isset($_GET['edit_link'])) { $res = mysqli_query($link, "SELECT * FROM LINKS WHERE link_id=".(int)$_GET['edit_link']); $edit_link = mysqli_fetch_assoc($res); }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gotham Metro Favs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #111; color: #eee; font-family: 'Segoe UI', sans-serif; padding-bottom: 80px; }
        .metro-tile { 
            aspect-ratio: 1/1; display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-decoration: none; color: white; transition: 0.2s; position: relative; border: none;
        }
        .metro-tile:hover { transform: scale(1.03); color: white; opacity: 0.9; }
        .tile-actions { position: absolute; top: 5px; right: 5px; display: none; }
        .metro-tile:hover .tile-actions { display: block; }
        .nav-metro { background: #000; border-bottom: 2px solid #0078d7; }
        footer { background: #000; position: fixed; bottom: 0; width: 100%; padding: 10px; font-size: 0.8rem; border-top: 1px solid #333; }
        .btn-metro { border-radius: 0; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand navbar-dark nav-metro mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-th"></i> GOTHAM FAVS</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link btn btn-outline-primary btn-sm me-2" href="#" data-bs-toggle="modal" data-bs-target="#modCat"> + Categoría</a>
            <a class="nav-link btn btn-outline-success btn-sm me-2" href="#" data-bs-toggle="modal" data-bs-target="#modLink"> + Favorito</a>
            <a class="nav-link text-danger" href="?logout=1"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</nav>

<div class="container">
    <?php
    $cats = mysqli_query($link, "SELECT * FROM LINK_CATEGORIES ORDER BY category_name ASC");
    while ($c = mysqli_fetch_assoc($cats)):
    ?>
    <div class="mb-4">
        <div class="d-flex align-items-center mb-2 border-bottom border-secondary pb-1">
            <h5 class="m-0 text-uppercase fw-lighter"><i class="<?= $c['category_icon'] ?>"></i> <?= $c['category_name'] ?></h5>
            <div class="ms-auto">
                <a href="?edit_cat=<?= $c['category_id'] ?>" class="text-info me-2 small"><i class="fas fa-edit"></i></a>
                <a href="?del_cat=<?= $c['category_id'] ?>" class="text-danger small" onclick="return confirm('¿Borrar categoría y sus links?')"><i class="fas fa-trash"></i></a>
            </div>
        </div>
        <div class="row g-2">
            <?php
            $cid = $c['category_id'];
            $links = mysqli_query($link, "SELECT * FROM LINKS WHERE category_id = $cid ORDER BY link_title ASC");
            while ($l = mysqli_fetch_assoc($links)):
            ?>
            <div class="col-4 col-sm-3 col-md-2">
                <div class="metro-tile" style="background-color: <?= $c['category_color'] ?>;">
                    <a href="<?= $l['link_url'] ?>" target="_blank" class="text-white text-decoration-none text-center p-2">
                        <i class="<?= $c['category_icon'] ?> fa-2x mb-1"></i><br>
                        <small class="d-block text-truncate fw-bold" style="max-width: 90px;"><?= $l['link_title'] ?></small>
                    </a>
                    <div class="tile-actions">
                        <a href="?edit_link=<?= $l['link_id'] ?>" class="badge bg-black text-info"><i class="fas fa-edit"></i></a>
                        <a href="?del_link=<?= $l['link_id'] ?>" class="badge bg-black text-danger" onclick="return confirm('¿Borrar?')"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<div class="modal fade" id="modCat" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content text-dark"><div class="modal-header">Gestión de Categoría</div>
    <div class="modal-body">
        <input type="hidden" name="cat_id" value="<?= $edit_cat['category_id'] ?? '' ?>">
        <label>Nombre:</label><input type="text" name="category_name" class="form-control mb-2" value="<?= $edit_cat['category_name'] ?? '' ?>" required>
        <label>Icono (FA class):</label><input type="text" name="category_icon" class="form-control mb-2" value="<?= $edit_cat['category_icon'] ?? 'fas fa-link' ?>" placeholder="fas fa-star">
        <label>Color (Hex o Nombre):</label><input type="color" name="category_color" class="form-control form-control-color w-100 mb-2" value="<?= $edit_cat['category_color'] ?? '#0078d7' ?>">
    </div>
    <div class="modal-footer"><button type="submit" name="save_cat" class="btn btn-primary w-100">Guardar Categoría</button></div>
</form></div></div>

<div class="modal fade" id="modLink" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content text-dark"><div class="modal-header">Gestión de Favorito</div>
    <div class="modal-body">
        <input type="hidden" name="lid" value="<?= $edit_link['link_id'] ?? '' ?>">
        <label>Categoría:</label>
        <select name="category_id" class="form-select mb-2">
            <?php 
            $cats_sel = mysqli_query($link, "SELECT * FROM LINK_CATEGORIES");
            while($cs = mysqli_fetch_assoc($cats_sel)) {
                $sel = ($cs['category_id'] == $edit_link['category_id']) ? 'selected' : '';
                echo "<option value='{$cs['category_id']}' $sel>{$cs['category_name']}</option>";
            }
            ?>
        </select>
        <label>Título:</label><input type="text" name="link_title" class="form-control mb-2" value="<?= $edit_link['link_title'] ?? '' ?>" required>
        <label>URL:</label><input type="url" name="link_url" class="form-control mb-2" value="<?= $edit_link['link_url'] ?? '' ?>" required>
    </div>
    <div class="modal-footer"><button type="submit" name="save_link" class="btn btn-success w-100">Guardar Favorito</button></div>
</form></div></div>

<footer>
    <div class="container d-flex justify-content-between">
        <span><b>PHP Version:</b> <?= phpversion(); ?></span>
        <span>Gotham v1.0 | 2026</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if($edit_cat): ?><script>new bootstrap.Modal('#modCat').show();</script><?php endif; ?>
<?php if($edit_link): ?><script>new bootstrap.Modal('#modLink').show();</script><?php endif; ?>
</body>
</html>
