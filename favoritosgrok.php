<?php 
session_start();

// ===============================================
// INCLUIR TU CONFIG.PHP ORIGINAL
// ===============================================
require_once 'config.php';  // ← tu archivo con check_authorization() y $link

// ===============================================
// CONFIGURACIÓN ADICIONAL DE SEGURIDAD
// ===============================================

$password_hardcoded   = 'gotham4feb*';          // cámbiala cuando quieras
$bypass_for_testing   = true;                   // ← cambiar a false en producción real

$client_ip = $_SERVER['REMOTE_ADDR'];

// Si estamos en modo prueba → saltamos toda la verificación
if ($bypass_for_testing) {
    $logged_in = true;
} else {
    // Verificamos si la IP está autorizada (tu función original)
    $allowed_ips = unserialize(AUTHORIZED_IPS);
    $ip_authorized = in_array($client_ip, $allowed_ips);

    // Si IP autorizada → logueado automáticamente
    if ($ip_authorized) {
        $logged_in = true;
    }
    // Si no → verificamos sesión o contraseña
    else {
        $logged_in = isset($_SESSION['authorized']) && $_SESSION['authorized'] === true;

        // Procesar formulario de contraseña
        if (isset($_POST['pass']) && $_POST['pass'] === $password_hardcoded) {
            $_SESSION['authorized'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

// Si NO está logueado → mostramos pantalla de contraseña
if (!$logged_in) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso Restringido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white d-flex align-items-center justify-content-center vh-100">
    <div class="card bg-dark text-white p-4 shadow" style="max-width:420px;">
        <h4 class="text-center mb-4"><i class="fas fa-shield-halved"></i> Acceso Protegido</h4>
        <p class="text-center text-muted mb-4">IP detectada: <?php echo htmlspecialchars($client_ip); ?></p>
        <?php if (isset($_POST['pass'])): ?>
            <div class="alert alert-danger">Contraseña incorrecta. Intenta de nuevo.</div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <input type="password" name="pass" class="form-control bg-secondary text-white border-0" placeholder="Contraseña" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

// ===============================================
// LOGOUT
// ===============================================
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ===============================================
// PROCESAMIENTO DE ACCIONES (POST)
// ===============================================

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Categorías
    if ($action === 'add_category' || $action === 'edit_category') {
        $name  = trim($_POST['category_name'] ?? '');
        $icon  = trim($_POST['category_icon'] ?? 'fa-solid fa-folder');
        $color = trim($_POST['category_color'] ?? '#0d6efd');

        if ($name !== '') {
            if ($action === 'add_category') {
                $stmt = $link->prepare("INSERT INTO LINK_CATEGORIES (category_name, category_icon, category_color) VALUES (?,?,?)");
                $stmt->bind_param("sss", $name, $icon, $color);
                $stmt->execute();
                $message = '<div class="alert alert-success">Categoría creada correctamente.</div>';
            } else {
                $id = (int)($_POST['category_id'] ?? 0);
                if ($id > 0) {
                    $stmt = $link->prepare("UPDATE LINK_CATEGORIES SET category_name=?, category_icon=?, category_color=? WHERE category_id=?");
                    $stmt->bind_param("sssi", $name, $icon, $color, $id);
                    $stmt->execute();
                    $message = '<div class="alert alert-success">Categoría actualizada.</div>';
                }
            }
        }
    }

    elseif ($action === 'delete_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        if ($id > 0) {
            $link->query("DELETE FROM LINK_CATEGORIES WHERE category_id = $id");
            $message = '<div class="alert alert-success">Categoría eliminada (enlaces asociados también).</div>';
        }
    }

    // Enlaces / Favoritos
    elseif ($action === 'add_link' || $action === 'edit_link') {
        $title   = trim($_POST['link_title']   ?? '');
        $url     = trim($_POST['link_url']     ?? '');
        $comment = trim($_POST['link_comment'] ?? '');
        $cat_id  = (int)($_POST['category_id'] ?? 0);

        if ($title !== '' && filter_var($url, FILTER_VALIDATE_URL) && $cat_id > 0) {
            if ($action === 'add_link') {
                $stmt = $link->prepare("INSERT INTO LINKS (category_id, link_title, link_url, link_comment) VALUES (?,?,?,?)");
                $stmt->bind_param("isss", $cat_id, $title, $url, $comment);
                $stmt->execute();
                $message = '<div class="alert alert-success">Favorito agregado.</div>';
            } else {
                $id = (int)($_POST['link_id'] ?? 0);
                if ($id > 0) {
                    $stmt = $link->prepare("UPDATE LINKS SET category_id=?, link_title=?, link_url=?, link_comment=? WHERE link_id=?");
                    $stmt->bind_param("isssi", $cat_id, $title, $url, $comment, $id);
                    $stmt->execute();
                    $message = '<div class="alert alert-success">Favorito actualizado.</div>';
                }
            }
        } else {
            $message = '<div class="alert alert-warning">Datos incompletos o URL inválida.</div>';
        }
    }

    elseif ($action === 'delete_link') {
        $id = (int)($_POST['link_id'] ?? 0);
        if ($id > 0) {
            $link->query("DELETE FROM LINKS WHERE link_id = $id");
            $message = '<div class="alert alert-success">Favorito eliminado.</div>';
        }
    }
}

// ===============================================
// CARGA DE DATOS PARA LA INTERFAZ
// ===============================================

$categories = $link->query("SELECT * FROM LINK_CATEGORIES ORDER BY category_name ASC");

$edit_category = null;
if (isset($_GET['edit_cat'])) {
    $id = (int)$_GET['edit_cat'];
    $res = $link->query("SELECT * FROM LINK_CATEGORIES WHERE category_id = $id LIMIT 1");
    $edit_category = $res->fetch_assoc();
}

$edit_link = null;
if (isset($_GET['edit_link'])) {
    $id = (int)$_GET['edit_link'];
    $res = $link->query("SELECT * FROM LINKS WHERE link_id = $id LIMIT 1");
    $edit_link = $res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis Favoritos - Alfonso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { padding-top: 70px; padding-bottom: 60px; background: #121212; }
        .metro-tile {
            height: 160px; width: 160px; margin: 12px;
            border-radius: 10px; color: white;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            transition: all 0.2s ease;
        }
        .metro-tile:hover { transform: translateY(-8px); box-shadow: 0 10px 25px rgba(0,0,0,0.6); }
        .metro-tile i { font-size: 2.8rem; margin-bottom: 10px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow fixed-top">
    <div class="container-fluid px-3">
        <a class="navbar-brand fw-bold" href="#">Favoritos</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="#vista"><i class="fas fa-th-large me-1"></i>Vista</a></li>
                <li class="nav-item"><a class="nav-link" href="#categorias"><i class="fas fa-folder me-1"></i>Categorías</a></li>
                <li class="nav-item"><a class="nav-link" href="#enlaces"><i class="fas fa-link me-1"></i>Favoritos</a></li>
            </ul>
            <span class="navbar-text me-3 text-muted small d-none d-md-inline">IP: <?php echo htmlspecialchars($client_ip); ?></span>
            <a href="?logout=1" class="btn btn-sm btn-outline-danger"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </div>
</nav>

<div class="container">

    <?php echo $message; ?>

    <!-- VISTA RÁPIDA -->
    <section id="vista" class="mb-5">
        <h4 class="mb-4 text-light"><i class="fas fa-th-large me-2"></i>Vista rápida (Metro)</h4>

        <div class="d-none d-md-flex flex-wrap justify-content-center">
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <a href="#cat-<?php echo $cat['category_id']; ?>" class="metro-tile text-decoration-none" style="background: <?php echo htmlspecialchars($cat['category_color']); ?>;">
                    <i class="<?php echo htmlspecialchars($cat['category_icon']); ?>"></i>
                    <div class="fs-6 fw-medium"><?php echo htmlspecialchars($cat['category_name']); ?></div>
                </a>
            <?php endwhile; $categories->data_seek(0); ?>
        </div>

        <div class="accordion d-md-none" id="accordionVista">
            <?php $i = 0; while ($cat = $categories->fetch_assoc()): $i++; ?>
                <div class="accordion-item border-0 mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?php if($i > 1) echo 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#acc<?php echo $i; ?>" style="background: <?php echo htmlspecialchars($cat['category_color']); ?>; color:white;">
                            <i class="<?php echo htmlspecialchars($cat['category_icon']); ?> me-3 fs-4"></i>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </button>
                    </h2>
                    <div id="acc<?php echo $i; ?>" class="accordion-collapse collapse <?php if($i === 1) echo 'show'; ?>">
                        <div class="accordion-body bg-dark">
                            <?php
                            $links = $link->query("SELECT * FROM LINKS WHERE category_id = {$cat['category_id']} ORDER BY link_title ASC");
                            if ($links->num_rows > 0): ?>
                                <ul class="list-group list-group-flush">
                                    <?php while ($lnk = $links->fetch_assoc()): ?>
                                        <li class="list-group-item bg-transparent border-bottom border-secondary">
                                            <a href="<?php echo htmlspecialchars($lnk['link_url']); ?>" target="_blank" class="text-light text-decoration-none">
                                                <strong><?php echo htmlspecialchars($lnk['link_title']); ?></strong>
                                                <?php if ($lnk['link_comment']): ?>
                                                    <small class="d-block text-muted"><?php echo htmlspecialchars($lnk['link_comment']); ?></small>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted fst-italic">No hay favoritos en esta categoría</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- CATEGORÍAS -->
    <section id="categorias" class="mb-5">
        <h4 class="mb-4 text-light"><i class="fas fa-folder me-2"></i>Gestionar Categorías</h4>

        <form method="post" class="mb-4 p-3 bg-dark border border-secondary rounded">
            <input type="hidden" name="action" value="<?php echo $edit_category ? 'edit_category' : 'add_category'; ?>">
            <?php if ($edit_category): ?>
                <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id']; ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="category_name" class="form-control" placeholder="Nombre categoría" value="<?php echo $edit_category ? htmlspecialchars($edit_category['category_name']) : ''; ?>" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="category_icon" class="form-control" placeholder="fa-solid fa-..." value="<?php echo $edit_category ? htmlspecialchars($edit_category['category_icon']) : 'fa-solid fa-folder'; ?>" required>
                </div>
                <div class="col-md-3">
                    <input type="color" name="category_color" class="form-control form-control-color w-100" value="<?php echo $edit_category ? htmlspecialchars($edit_category['category_color']) : '#0d6efd'; ?>">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-success"><?php echo $edit_category ? 'Actualizar' : 'Crear'; ?></button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-dark table-hover table-striped">
                <thead>
                    <tr><th>Color</th><th>Icono</th><th>Nombre</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                        <tr>
                            <td style="background:<?php echo htmlspecialchars($cat['category_color']); ?>;width:50px;"></td>
                            <td><i class="<?php echo htmlspecialchars($cat['category_icon']); ?>"></i></td>
                            <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                            <td>
                                <a href="?edit_cat=<?php echo $cat['category_id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar categoría y todos sus favoritos?');">
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- FAVORITOS -->
    <section id="enlaces">
        <h4 class="mb-4 text-light"><i class="fas fa-link me-2"></i>Gestionar Favoritos</h4>

        <form method="post" class="mb-4 p-3 bg-dark border border-secondary rounded">
            <input type="hidden" name="action" value="<?php echo $edit_link ? 'edit_link' : 'add_link'; ?>">
            <?php if ($edit_link): ?>
                <input type="hidden" name="link_id" value="<?php echo $edit_link['link_id']; ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="link_title" class="form-control" placeholder="Título del favorito" value="<?php echo $edit_link ? htmlspecialchars($edit_link['link_title']) : ''; ?>" required>
                </div>
                <div class="col-md-5">
                    <input type="url" name="link_url" class="form-control" placeholder="https://..." value="<?php echo $edit_link ? htmlspecialchars($edit_link['link_url']) : ''; ?>" required>
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select" required>
                        <option value="">— Elige categoría —</option>
                        <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php if ($edit_link && $edit_link['category_id'] == $cat['category_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-12">
                    <textarea name="link_comment" class="form-control" rows="2" placeholder="Notas / comentarios (opcional)"><?php echo $edit_link ? htmlspecialchars($edit_link['link_comment'] ?? '') : ''; ?></textarea>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success px-5"><?php echo $edit_link ? 'Actualizar favorito' : 'Agregar favorito'; ?></button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-dark table-hover table-striped">
                <thead>
                    <tr><th>Título</th><th>Categoría</th><th>URL</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php
                    $all_links = $link->query("
                        SELECT l.*, c.category_name, c.category_color 
                        FROM LINKS l 
                        LEFT JOIN LINK_CATEGORIES c ON l.category_id = c.category_id 
                        ORDER BY c.category_name ASC, l.link_title ASC
                    ");
                    while ($row = $all_links->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['link_title']); ?></td>
                            <td><span class="badge" style="background:<?php echo htmlspecialchars($row['category_color'] ?: '#6c757d'); ?>">
                                <?php echo htmlspecialchars($row['category_name'] ?: 'Sin categoría'); ?>
                            </span></td>
                            <td><a href="<?php echo htmlspecialchars($row['link_url']); ?>" target="_blank" class="text-info"><?php echo htmlspecialchars($row['link_url']); ?></a></td>
                            <td>
                                <a href="?edit_link=<?php echo $row['link_id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este favorito?');">
                                    <input type="hidden" name="action" value="delete_link">
                                    <input type="hidden" name="link_id" value="<?php echo $row['link_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<footer class="fixed-bottom bg-black text-white text-center py-2 small">
    PHP <?php echo phpversion(); ?> • IP: <?php echo htmlspecialchars($client_ip); ?> • <?php echo date('Y'); ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
