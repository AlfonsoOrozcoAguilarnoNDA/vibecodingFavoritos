<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// ===============================================
// SEGURIDAD: IP o contraseña hardcoded
// ===============================================
$authorized_ips = unserialize(AUTHORIZED_IPS);
$client_ip = $_SERVER['REMOTE_ADDR'];
$hardcoded_password = "gotham4feb*";

if (!in_array($client_ip, $authorized_ips)) {
    if (!isset($_SESSION['auth'])) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            if ($_POST['password'] === $hardcoded_password) {
                $_SESSION['auth'] = true;
            } else {
                $error = "Contraseña incorrecta.";
            }
        }
        if (!isset($_SESSION['auth'])) {
            ?>
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Acceso</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
            </head>
            <body class="d-flex justify-content-center align-items-center vh-100">
                <form method="post" class="border p-4 bg-light">
                    <h4><i class="fas fa-lock"></i> Acceso</h4>
                    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <input type="password" name="password" class="form-control mb-2" placeholder="Contraseña">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </form>
            </body>
            </html>
            <?php
            exit;
        }
    }
}

// ===============================================
// CRUD: Categorías y Links
// ===============================================
function get_categories() {
    global $link;
    $sql = "SELECT * FROM LINK_CATEGORIES ORDER BY category_name ASC";
    return $link->query($sql);
}

function get_links_by_category($category_id) {
    global $link;
    $sql = "SELECT * FROM LINKS WHERE category_id=$category_id ORDER BY link_title ASC";
    return $link->query($sql);
}

// Procesar formularios (altas/bajas/cambios)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Alta de categoría
    if (isset($_POST['add_category'])) {
        $name = $link->real_escape_string($_POST['category_name']);
        $icon = $link->real_escape_string($_POST['category_icon']);
        $color = $link->real_escape_string($_POST['category_color']);
        $link->query("INSERT INTO LINK_CATEGORIES (category_name, category_icon, category_color) VALUES ('$name','$icon','$color')");
    }
    // Alta de link
    if (isset($_POST['add_link'])) {
        $title = $link->real_escape_string($_POST['link_title']);
        $url = $link->real_escape_string($_POST['link_url']);
        $comment = $link->real_escape_string($_POST['link_comment']);
        $cat = intval($_POST['category_id']);
        $link->query("INSERT INTO LINKS (category_id, link_title, link_url, link_comment) VALUES ($cat,'$title','$url','$comment')");
    }
    // Borrar categoría
    if (isset($_POST['delete_category'])) {
        $id = intval($_POST['category_id']);
        $link->query("DELETE FROM LINK_CATEGORIES WHERE category_id=$id");
    }
    // Borrar link
    if (isset($_POST['delete_link'])) {
        $id = intval($_POST['link_id']);
        $link->query("DELETE FROM LINKS WHERE link_id=$id");
    }
}

// ===============================================
// INTERFAZ HTML
// ===============================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Favoritos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { padding-top: 70px; }
        footer { position: fixed; bottom: 0; width: 100%; background: #f8f9fa; text-align: center; padding: 5px; }
        .metro-blue { background-color:#0078d7; color:white; }
        .metro-green { background-color:#107c10; color:white; }
        .metro-red { background-color:#d13438; color:white; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Favoritos</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="#categorias">Categorías</a></li>
                <li class="nav-item"><a class="nav-link" href="#links">Links</a></li>
                <?php if (!in_array($client_ip, $authorized_ips)): ?>
                <li class="nav-item"><a class="nav-link" href="?logout=1">Salir</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h2 id="categorias">Categorías</h2>
    <form method="post" class="row g-2 mb-3">
        <div class="col"><input type="text" name="category_name" class="form-control" placeholder="Nombre"></div>
        <div class="col"><input type="text" name="category_icon" class="form-control" placeholder="Icono (fa-star)"></div>
        <div class="col"><input type="text" name="category_color" class="form-control" placeholder="Color (metro-blue)"></div>
        <div class="col"><button type="submit" name="add_category" class="btn btn-success">Agregar</button></div>
    </form>
    <div class="row">
        <?php $cats = get_categories(); while($cat=$cats->fetch_assoc()): ?>
            <div class="col-md-3 mb-2">
                <div class="card <?php echo $cat['category_color']; ?>">
                    <div class="card-body">
                        <i class="fa <?php echo $cat['category_icon']; ?>"></i>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                            <button type="submit" name="delete_category" class="btn btn-sm btn-danger float-end">X</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <h2 id="links">Links</h2>
    <form method="post" class="row g-2 mb-3">
        <div class="col"><input type="text" name="link_title" class="form-control" placeholder="Título"></div>
        <div class="col"><input type="text" name="link_url" class="form-control" placeholder="URL"></div>
        <div class="col"><input type="text" name="link_comment" class="form-control" placeholder="Comentario"></div>
        <div class="col">
            <select name="category_id" class="form-select">
                <?php $cats = get_categories(); while($cat=$cats->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col"><button type="submit" name="add_link" class="btn btn-success">Agregar</button></div>
    </form>
    <?php $cats = get_categories(); while($cat=$cats->fetch_assoc()): ?>
        <h4><i class="fa <?php echo $cat['category_icon']; ?>"></i> <?php echo htmlspecialchars($cat['category_name']); ?></h4>
        <ul class="list-group mb-3">
            <?php $links = get_links_by_category($cat['category_id']); while($ln=$links->fetch_assoc()): ?>
                <li class="list-group-item">
                    <a href="<?php echo htmlspecialchars($ln['link_url']); ?>" target="_blank"><?php echo htmlspecialchars($ln['link_title']); ?></a>
                    <small><?php echo htmlspecialchars
