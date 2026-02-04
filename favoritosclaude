<?php
// ===============================================
// SISTEMA DE GESTIÓN DE FAVORITOS
// ===============================================
// Archivo único con toda la funcionalidad
// Requiere: config.php, Bootstrap 5, Font Awesome 6

require_once 'config.php';

// -----------------------------------------------
// CONFIGURACIÓN Y CONSTANTES
// -----------------------------------------------
define('PASSWORD_HARDCODED', 'gotham4feb*');
define('SESSION_KEY', 'favoritos_authenticated');

// MODO DE PRUEBA: Cambiar a false para activar validación de IP/contraseña
define('TEST_MODE', true);

// -----------------------------------------------
// FUNCIONES DE AUTENTICACIÓN
// -----------------------------------------------

/**
 * Verifica si el usuario está autenticado
 */
function is_authenticated() {
    if (TEST_MODE) {
        return true; // En modo prueba, siempre autenticado
    }
    
    // Verificar IP autorizada
    $client_ip = $_SERVER['REMOTE_ADDR'];
    $allowed_ips = unserialize(AUTHORIZED_IPS);
    
    if (in_array($client_ip, $allowed_ips)) {
        return true;
    }
    
    // Si no es IP autorizada, verificar sesión
    return isset($_SESSION[SESSION_KEY]) && $_SESSION[SESSION_KEY] === true;
}

/**
 * Procesa el login con contraseña
 */
function process_login() {
    if (isset($_POST['login_password'])) {
        if ($_POST['login_password'] === PASSWORD_HARDCODED) {
            $_SESSION[SESSION_KEY] = true;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            return 'Contraseña incorrecta';
        }
    }
    return null;
}

/**
 * Procesa el logout
 */
function process_logout() {
    if (isset($_GET['logout'])) {
        unset($_SESSION[SESSION_KEY]);
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// -----------------------------------------------
// FUNCIONES DE BASE DE DATOS - CATEGORÍAS
// -----------------------------------------------

/**
 * Obtener todas las categorías ordenadas alfabéticamente
 */
function get_all_categories() {
    global $link;
    $sql = "SELECT * FROM LINK_CATEGORIES ORDER BY category_name ASC";
    $result = $link->query($sql);
    $categories = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

/**
 * Obtener una categoría por ID
 */
function get_category_by_id($id) {
    global $link;
    $id = intval($id);
    $sql = "SELECT * FROM LINK_CATEGORIES WHERE category_id = $id";
    $result = $link->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

/**
 * Crear nueva categoría
 */
function create_category($name, $icon, $color) {
    global $link;
    $name = $link->real_escape_string(trim($name));
    $icon = $link->real_escape_string(trim($icon));
    $color = $link->real_escape_string(trim($color));
    
    $sql = "INSERT INTO LINK_CATEGORIES (category_name, category_icon, category_color) 
            VALUES ('$name', '$icon', '$color')";
    return $link->query($sql);
}

/**
 * Actualizar categoría existente
 */
function update_category($id, $name, $icon, $color) {
    global $link;
    $id = intval($id);
    $name = $link->real_escape_string(trim($name));
    $icon = $link->real_escape_string(trim($icon));
    $color = $link->real_escape_string(trim($color));
    
    $sql = "UPDATE LINK_CATEGORIES 
            SET category_name = '$name', category_icon = '$icon', category_color = '$color' 
            WHERE category_id = $id";
    return $link->query($sql);
}

/**
 * Eliminar categoría
 */
function delete_category($id) {
    global $link;
    $id = intval($id);
    $sql = "DELETE FROM LINK_CATEGORIES WHERE category_id = $id";
    return $link->query($sql);
}

// -----------------------------------------------
// FUNCIONES DE BASE DE DATOS - LINKS
// -----------------------------------------------

/**
 * Obtener todos los links ordenados por categoría y título
 */
function get_all_links() {
    global $link;
    $sql = "SELECT l.*, c.category_name, c.category_icon, c.category_color 
            FROM LINKS l 
            INNER JOIN LINK_CATEGORIES c ON l.category_id = c.category_id 
            ORDER BY c.category_name ASC, l.link_title ASC";
    $result = $link->query($sql);
    $links = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $links[] = $row;
        }
    }
    return $links;
}

/**
 * Obtener links agrupados por categoría
 */
function get_links_by_category() {
    global $link;
    $sql = "SELECT l.*, c.category_name, c.category_icon, c.category_color 
            FROM LINKS l 
            INNER JOIN LINK_CATEGORIES c ON l.category_id = c.category_id 
            ORDER BY c.category_name ASC, l.link_title ASC";
    $result = $link->query($sql);
    $grouped = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cat_id = $row['category_id'];
            if (!isset($grouped[$cat_id])) {
                $grouped[$cat_id] = [
                    'info' => [
                        'category_id' => $row['category_id'],
                        'category_name' => $row['category_name'],
                        'category_icon' => $row['category_icon'],
                        'category_color' => $row['category_color']
                    ],
                    'links' => []
                ];
            }
            $grouped[$cat_id]['links'][] = $row;
        }
    }
    return $grouped;
}

/**
 * Obtener un link por ID
 */
function get_link_by_id($id) {
    global $link;
    $id = intval($id);
    $sql = "SELECT * FROM LINKS WHERE link_id = $id";
    $result = $link->query($sql);
    return $result ? $result->fetch_assoc() : null;
}

/**
 * Crear nuevo link
 */
function create_link($category_id, $title, $url, $comment) {
    global $link;
    $category_id = intval($category_id);
    $title = $link->real_escape_string(trim($title));
    $url = $link->real_escape_string(trim($url));
    $comment = $link->real_escape_string(trim($comment));
    
    $sql = "INSERT INTO LINKS (category_id, link_title, link_url, link_comment) 
            VALUES ($category_id, '$title', '$url', '$comment')";
    return $link->query($sql);
}

/**
 * Actualizar link existente
 */
function update_link($id, $category_id, $title, $url, $comment) {
    global $link;
    $id = intval($id);
    $category_id = intval($category_id);
    $title = $link->real_escape_string(trim($title));
    $url = $link->real_escape_string(trim($url));
    $comment = $link->real_escape_string(trim($comment));
    
    $sql = "UPDATE LINKS 
            SET category_id = $category_id, link_title = '$title', 
                link_url = '$url', link_comment = '$comment' 
            WHERE link_id = $id";
    return $link->query($sql);
}

/**
 * Eliminar link
 */
function delete_link($id) {
    global $link;
    $id = intval($id);
    $sql = "DELETE FROM LINKS WHERE link_id = $id";
    return $link->query($sql);
}

// -----------------------------------------------
// PROCESAMIENTO DE FORMULARIOS
// -----------------------------------------------

$message = '';
$message_type = 'success';

// Logout
process_logout();

// Login
$login_error = process_login();

// Procesar acciones solo si está autenticado
if (is_authenticated()) {
    
    // === CATEGORÍAS ===
    if (isset($_POST['action_category'])) {
        switch ($_POST['action_category']) {
            case 'create':
                if (create_category($_POST['cat_name'], $_POST['cat_icon'], $_POST['cat_color'])) {
                    $message = 'Categoría creada exitosamente';
                } else {
                    $message = 'Error al crear categoría';
                    $message_type = 'danger';
                }
                break;
                
            case 'update':
                if (update_category($_POST['cat_id'], $_POST['cat_name'], $_POST['cat_icon'], $_POST['cat_color'])) {
                    $message = 'Categoría actualizada exitosamente';
                } else {
                    $message = 'Error al actualizar categoría';
                    $message_type = 'danger';
                }
                break;
                
            case 'delete':
                if (delete_category($_POST['cat_id'])) {
                    $message = 'Categoría eliminada exitosamente';
                } else {
                    $message = 'Error al eliminar categoría';
                    $message_type = 'danger';
                }
                break;
        }
    }
    
    // === LINKS ===
    if (isset($_POST['action_link'])) {
        switch ($_POST['action_link']) {
            case 'create':
                if (create_link($_POST['link_category'], $_POST['link_title'], $_POST['link_url'], $_POST['link_comment'])) {
                    $message = 'Favorito creado exitosamente';
                } else {
                    $message = 'Error al crear favorito';
                    $message_type = 'danger';
                }
                break;
                
            case 'update':
                if (update_link($_POST['link_id'], $_POST['link_category'], $_POST['link_title'], $_POST['link_url'], $_POST['link_comment'])) {
                    $message = 'Favorito actualizado exitosamente';
                } else {
                    $message = 'Error al actualizar favorito';
                    $message_type = 'danger';
                }
                break;
                
            case 'delete':
                if (delete_link($_POST['link_id'])) {
                    $message = 'Favorito eliminado exitosamente';
                } else {
                    $message = 'Error al eliminar favorito';
                    $message_type = 'danger';
                }
                break;
        }
    }
}

// Obtener datos para mostrar
$categories = get_all_categories();
$links_grouped = get_links_by_category();

// Datos para edición
$edit_category = isset($_GET['edit_cat']) ? get_category_by_id($_GET['edit_cat']) : null;
$edit_link = isset($_GET['edit_link']) ? get_link_by_id($_GET['edit_link']) : null;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Favoritos</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --metro-blue: #0078d7;
            --metro-green: #10893e;
            --metro-red: #e81123;
            --metro-orange: #ff8c00;
            --metro-purple: #881798;
            --metro-teal: #00b7c3;
            --metro-pink: #e3008c;
            --metro-yellow: #fff100;
            --metro-brown: #825a2c;
            --metro-gray: #5d5d5d;
        }
        
        body {
            padding-top: 70px;
            padding-bottom: 60px;
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Navbar fija */
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Footer fijo */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #343a40;
            color: white;
            padding: 10px 0;
            text-align: center;
            font-size: 0.9rem;
            z-index: 1000;
        }
        
        /* Tarjetas estilo Metro */
        .metro-card {
            border: none;
            border-radius: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .metro-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .metro-card .card-header {
            border: none;
            border-radius: 0;
            font-weight: 600;
            padding: 15px 20px;
            color: white;
        }
        
        /* Colores Metro para categorías */
        .metro-blue { background-color: var(--metro-blue) !important; }
        .metro-green { background-color: var(--metro-green) !important; }
        .metro-red { background-color: var(--metro-red) !important; }
        .metro-orange { background-color: var(--metro-orange) !important; }
        .metro-purple { background-color: var(--metro-purple) !important; }
        .metro-teal { background-color: var(--metro-teal) !important; }
        .metro-pink { background-color: var(--metro-pink) !important; }
        .metro-yellow { background-color: var(--metro-yellow) !important; color: #333 !important; }
        .metro-brown { background-color: var(--metro-brown) !important; }
        .metro-gray { background-color: var(--metro-gray) !important; }
        
        /* Botones Metro */
        .btn-metro {
            border-radius: 0;
            border: none;
            padding: 10px 20px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        /* Links de favoritos */
        .link-item {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s;
        }
        
        .link-item:last-child {
            border-bottom: none;
        }
        
        .link-item:hover {
            background-color: #f8f9fa;
        }
        
        .link-item a {
            color: #212529;
            text-decoration: none;
            font-weight: 500;
        }
        
        .link-item a:hover {
            color: var(--metro-blue);
        }
        
        .link-comment {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        /* Formularios */
        .form-control, .form-select {
            border-radius: 0;
        }
        
        /* Login */
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1rem;
            }
            
            body {
                padding-top: 60px;
            }
        }
        
        /* Badge para acciones */
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-buttons .btn {
            padding: 3px 8px;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>

<?php if (!is_authenticated()): ?>
    
    <!-- PANTALLA DE LOGIN -->
    <div class="container">
        <div class="login-container">
            <div class="card metro-card">
                <div class="card-header metro-blue">
                    <i class="fas fa-lock"></i> Acceso Restringido
                </div>
                <div class="card-body">
                    <p class="text-muted">Tu IP no está autorizada. Ingresa la contraseña para continuar.</p>
                    
                    <?php if ($login_error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $login_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="login_password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="login_password" name="login_password" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary btn-metro w-100">
                            <i class="fas fa-sign-in-alt"></i> Ingresar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#favoritos">
                <i class="fas fa-star"></i> Gestor de Favoritos
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#favoritos"><i class="fas fa-bookmark"></i> Favoritos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gestion-links"><i class="fas fa-link"></i> Gestión Links</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gestion-categorias"><i class="fas fa-folder"></i> Gestión Categorías</a>
                    </li>
                    <?php if (!TEST_MODE): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="?logout=1">
                            <i class="fas fa-sign-out-alt"></i> Salir
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        
        <!-- MENSAJES -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- SECCIÓN: FAVORITOS -->
        <section id="favoritos" class="mb-5">
            <h2 class="mb-4"><i class="fas fa-bookmark"></i> Mis Favoritos</h2>
            
            <?php if (empty($links_grouped)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay favoritos registrados. Comienza agregando categorías y links.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($links_grouped as $category): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card metro-card">
                                <div class="card-header <?php echo htmlspecialchars($category['info']['category_color']); ?>">
                                    <i class="fas <?php echo htmlspecialchars($category['info']['category_icon']); ?>"></i>
                                    <?php echo htmlspecialchars($category['info']['category_name']); ?>
                                    <span class="badge bg-light text-dark float-end"><?php echo count($category['links']); ?></span>
                                </div>
                                <div class="card-body p-0">
                                    <?php foreach ($category['links'] as $link): ?>
                                        <div class="link-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <a href="<?php echo htmlspecialchars($link['link_url']); ?>" target="_blank">
                                                        <i class="fas fa-external-link-alt fa-sm"></i>
                                                        <?php echo htmlspecialchars($link['link_title']); ?>
                                                    </a>
                                                    <?php if ($link['link_comment']): ?>
                                                        <div class="link-comment"><?php echo htmlspecialchars($link['link_comment']); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="action-buttons">
                                                    <a href="#gestion-links" class="btn btn-sm btn-outline-primary" 
                                                       onclick="document.getElementById('edit_link_<?php echo $link['link_id']; ?>').scrollIntoView({behavior: 'smooth'});">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- SECCIÓN: GESTIÓN DE LINKS -->
        <section id="gestion-links" class="mb-5">
            <h2 class="mb-4"><i class="fas fa-link"></i> Gestión de Favoritos</h2>
            
            <div class="row">
                <!-- FORMULARIO: CREAR/EDITAR LINK -->
                <div class="col-lg-6">
                    <div class="card metro-card">
                        <div class="card-header metro-blue">
                            <i class="fas fa-<?php echo $edit_link ? 'edit' : 'plus'; ?>"></i>
                            <?php echo $edit_link ? 'Editar Favorito' : 'Nuevo Favorito'; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action_link" value="<?php echo $edit_link ? 'update' : 'create'; ?>">
                                <?php if ($edit_link): ?>
                                    <input type="hidden" name="link_id" value="<?php echo $edit_link['link_id']; ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="link_category" class="form-label">Categoría *</label>
                                    <select class="form-select" id="link_category" name="link_category" required>
                                        <option value="">Seleccionar categoría...</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['category_id']; ?>" 
                                                <?php echo ($edit_link && $edit_link['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['category_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="link_title" class="form-label">Título *</label>
                                    <input type="text" class="form-control" id="link_title" name="link_title" 
                                           value="<?php echo $edit_link ? htmlspecialchars($edit_link['link_title']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="link_url" class="form-label">URL *</label>
                                    <input type="url" class="form-control" id="link_url" name="link_url" 
                                           value="<?php echo $edit_link ? htmlspecialchars($edit_link['link_url']) : ''; ?>" 
                                           placeholder="https://..." required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="link_comment" class="form-label">Comentario</label>
                                    <textarea class="form-control" id="link_comment" name="link_comment" rows="2"><?php echo $edit_link ? htmlspecialchars($edit_link['link_comment']) : ''; ?></textarea>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-metro">
                                        <i class="fas fa-<?php echo $edit_link ? 'save' : 'plus'; ?>"></i>
                                        <?php echo $edit_link ? 'Actualizar' : 'Crear'; ?> Favorito
                                    </button>
                                    <?php if ($edit_link): ?>
                                        <a href="?" class="btn btn-secondary btn-metro">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- LISTA DE LINKS -->
                <div class="col-lg-6">
                    <div class="card metro-card">
                        <div class="card-header metro-gray">
                            <i class="fas fa-list"></i> Todos los Favoritos
                        </div>
                        <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                            <?php
                            $all_links = get_all_links();
                            if (empty($all_links)):
                            ?>
                                <div class="p-3 text-muted">No hay favoritos registrados</div>
                            <?php else: ?>
                                <?php foreach ($all_links as $link): ?>
                                    <div class="link-item" id="edit_link_<?php echo $link['link_id']; ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <strong><?php echo htmlspecialchars($link['link_title']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas <?php echo htmlspecialchars($link['category_icon']); ?>"></i>
                                                    <?php echo htmlspecialchars($link['category_name']); ?>
                                                </small>
                                                <br>
                                                <small class="text-break"><?php echo htmlspecialchars($link['link_url']); ?></small>
                                            </div>
                                            <div class="action-buttons">
                                                <a href="?edit_link=<?php echo $link['link_id']; ?>#gestion-links" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este favorito?');">
                                                    <input type="hidden" name="action_link" value="delete">
                                                    <input type="hidden" name="link_id" value="<?php echo $link['link_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- SECCIÓN: GESTIÓN DE CATEGORÍAS -->
        <section id="gestion-categorias" class="mb-5">
            <h2 class="mb-4"><i class="fas fa-folder"></i> Gestión de Categorías</h2>
            
            <div class="row">
                <!-- FORMULARIO: CREAR/EDITAR CATEGORÍA -->
                <div class="col-lg-6">
                    <div class="card metro-card">
                        <div class="card-header metro-green">
                            <i class="fas fa-<?php echo $edit_category ? 'edit' : 'plus'; ?>"></i>
                            <?php echo $edit_category ? 'Editar Categoría' : 'Nueva Categoría'; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action_category" value="<?php echo $edit_category ? 'update' : 'create'; ?>">
                                <?php if ($edit_category): ?>
                                    <input type="hidden" name="cat_id" value="<?php echo $edit_category['category_id']; ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="cat_name" class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="cat_name" name="cat_name" 
                                           value="<?php echo $edit_category ? htmlspecialchars($edit_category['category_name']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cat_icon" class="form-label">Icono (Font Awesome) *</label>
                                    <input type="text" class="form-control" id="cat_icon" name="cat_icon" 
                                           value="<?php echo $edit_category ? htmlspecialchars($edit_category['category_icon']) : 'fa-link'; ?>" 
                                           placeholder="fa-link" required>
                                    <small class="form-text text-muted">
                                        Busca iconos en: <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com/icons</a>
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cat_color" class="form-label">Color Metro *</label>
                                    <select class="form-select" id="cat_color" name="cat_color" required>
                                        <?php
                                        $colors = [
                                            'metro-blue' => 'Azul',
                                            'metro-green' => 'Verde',
                                            'metro-red' => 'Rojo',
                                            'metro-orange' => 'Naranja',
                                            'metro-purple' => 'Púrpura',
                                            'metro-teal' => 'Turquesa',
                                            'metro-pink' => 'Rosa',
                                            'metro-yellow' => 'Amarillo',
                                            'metro-brown' => 'Café',
                                            'metro-gray' => 'Gris'
                                        ];
                                        foreach ($colors as $value => $label):
                                        ?>
                                            <option value="<?php echo $value; ?>" 
                                                <?php echo ($edit_category && $edit_category['category_color'] == $value) ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-metro">
                                        <i class="fas fa-<?php echo $edit_category ? 'save' : 'plus'; ?>"></i>
                                        <?php echo $edit_category ? 'Actualizar' : 'Crear'; ?> Categoría
                                    </button>
                                    <?php if ($edit_category): ?>
                                        <a href="?" class="btn btn-secondary btn-metro">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- LISTA DE CATEGORÍAS -->
                <div class="col-lg-6">
                    <div class="card metro-card">
                        <div class="card-header metro-gray">
                            <i class="fas fa-list"></i> Todas las Categorías
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($categories)): ?>
                                <div class="p-3 text-muted">No hay categorías registradas</div>
                            <?php else: ?>
                                <?php foreach ($categories as $cat): ?>
                                    <div class="link-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge <?php echo htmlspecialchars($cat['category_color']); ?> me-2">
                                                    <i class="fas <?php echo htmlspecialchars($cat['category_icon']); ?>"></i>
                                                </span>
                                                <strong><?php echo htmlspecialchars($cat['category_name']); ?></strong>
                                            </div>
                                            <div class="action-buttons">
                                                <a href="?edit_cat=<?php echo $cat['category_id']; ?>#gestion-categorias" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar esta categoría y todos sus favoritos?');">
                                                    <input type="hidden" name="action_category" value="delete">
                                                    <input type="hidden" name="cat_id" value="<?php echo $cat['category_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
    </div>

<?php endif; ?>

<!-- FOOTER -->
<footer class="footer">
    <div class="container-fluid">
        <small>
            <i class="fas fa-server"></i> PHP Version: <strong><?php echo phpversion(); ?></strong>
            <?php if (TEST_MODE): ?>
                <span class="badge bg-warning text-dark ms-2">MODO PRUEBA</span>
            <?php endif; ?>
        </small>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script para vista previa del icono -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const iconInput = document.getElementById('cat_icon');
    const colorSelect = document.getElementById('cat_color');
    
    if (iconInput && colorSelect) {
        // Crear preview
        const preview = document.createElement('div');
        preview.className = 'mt-2 p-3 text-center';
        preview.style.display = 'inline-block';
        iconInput.parentElement.appendChild(preview);
        
        function updatePreview() {
            const icon = iconInput.value || 'fa-link';
            const color = colorSelect.value || 'metro-blue';
            preview.className = 'mt-2 p-3 text-center ' + color;
            preview.innerHTML = '<i class="fas ' + icon + ' fa-3x"></i>';
        }
        
        iconInput.addEventListener('input', updatePreview);
        colorSelect.addEventListener('change', updatePreview);
        updatePreview();
    }
});
</script>

</body>
</html>
