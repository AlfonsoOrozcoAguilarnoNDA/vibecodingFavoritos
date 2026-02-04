<?php
/**
 * ============================================================================
 * PROYECTO: vibecodingFavoritos (Edición Humana)
 * ============================================================================
 * AUTOR:         Alfonso Orozco Aguilar (vibecodingmexico.com)
 * PERFIL:        DevOps / Programador desde 1991 / Contaduría
 * FECHA:         04 de Febrero, 2026
 * REQUISITOS:    PHP 7.4 - 8.4+ | MySQL 5.7+ | cPanel Environment
 * LICENCIA:      MIT (Libre uso, mantener crédito del autor)
 * * OBJETIVO:
 * Crear un sistema de gestión de marcadores (bookmarks) que sea:
 * 1. Resiliente: Capaz de funcionar en redes inestables (Metro/Móvil).
 * 2. Ligero: Bajo consumo de RAM en hosting compartido (Economía de Trinchera). 
 * 3. Estético: Interfaz Metro sólida optimizada para pantallas táctiles.
 * 4. Seguro:  validación (Lista blanca de IP + Password Hardcoded).
 *    Este caso no necesito YO validación  por password, pero son tres líneas
 *    en caso de ser necesario.
 *
 * NOTA TÉCNICA:
 * Este código es el resultado de una auditoría forense contra modelos de IA 
 * (Claude, Grok, Gemini). Utiliza paradigma PROCEDURAL puro para máxima 
 * portabilidad y control de flujo sin sobreingeniería.
 *
 * DOCUMENTACIÓN Y COMPARATIVA:
 * https://vibecodingmexico.com/vibe-coding-favoritos/
 * ============================================================================
 */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once 'config.php';
// include_once 'ui_functions.php';

// Seguridad por IP / sesión
check_authorization();

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// ---------------------------------------------------------------------
// CREAR TABLAS SI NO EXISTEN
// ---------------------------------------------------------------------
$sql_create_categories = "
CREATE TABLE IF NOT EXISTS LINK_CATEGORIES (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    category_icon VARCHAR(50) NOT NULL DEFAULT 'fa-link',
    category_color VARCHAR(20) NOT NULL DEFAULT 'metro-blue',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$sql_create_links = "
CREATE TABLE IF NOT EXISTS LINKS (
    link_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    link_title VARCHAR(200) NOT NULL,
    link_url TEXT NOT NULL,
    link_comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES LINK_CATEGORIES(category_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

mysqli_query($link, $sql_create_categories);
mysqli_query($link, $sql_create_links);

// ---------------------------------------------------------------------
// PROCESAR ACCIONES
// ---------------------------------------------------------------------
$mensaje = '';
$tipo_mensaje = '';

// CREAR CATEGORÍA
if (isset($_POST['action']) && $_POST['action'] === 'create_category') {
    $cat_name = mysqli_real_escape_string($link, trim($_POST['category_name']));
    $cat_icon = mysqli_real_escape_string($link, trim($_POST['category_icon']));
    $cat_color = mysqli_real_escape_string($link, trim($_POST['category_color']));
    
    if (!empty($cat_name)) {
        $sql = "INSERT INTO LINK_CATEGORIES (category_name, category_icon, category_color) 
                VALUES ('$cat_name', '$cat_icon', '$cat_color')";
        if (mysqli_query($link, $sql)) {
            $mensaje = "Categoría creada exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al crear categoría: " . mysqli_error($link);
            $tipo_mensaje = "danger";
        }
    }
}

// ELIMINAR CATEGORÍA
if (isset($_GET['delete_category'])) {
    $cat_id = (int)$_GET['delete_category'];
    $sql = "DELETE FROM LINK_CATEGORIES WHERE category_id = $cat_id";
    if (mysqli_query($link, $sql)) {
        $mensaje = "Categoría eliminada exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al eliminar categoría: " . mysqli_error($link);
        $tipo_mensaje = "danger";
    }
}

// CREAR LINK
if (isset($_POST['action']) && $_POST['action'] === 'create_link') {
    $cat_id = (int)$_POST['link_category'];
    $link_title = mysqli_real_escape_string($link, trim($_POST['link_title']));
    $link_url = mysqli_real_escape_string($link, trim($_POST['link_url']));
    $link_comment = mysqli_real_escape_string($link, trim($_POST['link_comment']));
    
    if (!empty($link_title) && !empty($link_url) && $cat_id > 0) {
        $sql = "INSERT INTO LINKS (category_id, link_title, link_url, link_comment) 
                VALUES ($cat_id, '$link_title', '$link_url', '$link_comment')";
        if (mysqli_query($link, $sql)) {
            $mensaje = "Link creado exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al crear link: " . mysqli_error($link);
            $tipo_mensaje = "danger";
        }
    }
}

// ELIMINAR LINK
if (isset($_GET['delete_link'])) {
    $link_id = (int)$_GET['delete_link'];
    $sql = "DELETE FROM LINKS WHERE link_id = $link_id";
    if (mysqli_query($link, $sql)) {
        $mensaje = "Link eliminado exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al eliminar link: " . mysqli_error($link);
        $tipo_mensaje = "danger";
    }
}

// ---------------------------------------------------------------------
// OBTENER DATOS
// ---------------------------------------------------------------------

// Obtener todas las categorías
$sql_categories = "SELECT * FROM LINK_CATEGORIES ORDER BY category_name ASC";
$result_categories = mysqli_query($link, $sql_categories);
$categories = array();
if ($result_categories) {
    while ($row = mysqli_fetch_assoc($result_categories)) {
        $categories[] = $row;
    }
    mysqli_free_result($result_categories);
}

// Obtener todos los links agrupados por categoría
$links_by_category = array();
foreach ($categories as $cat) {
    $cat_id = (int)$cat['category_id'];
    $sql_links = "SELECT * FROM LINKS WHERE category_id = $cat_id ORDER BY created_at DESC";
    $result_links = mysqli_query($link, $sql_links);
    
    $links_by_category[$cat_id] = array(
        'category_info' => $cat,
        'links' => array()
    );
    
    if ($result_links) {
        while ($row = mysqli_fetch_assoc($result_links)) {
            $links_by_category[$cat_id]['links'][] = $row;
        }
        mysqli_free_result($result_links);
    }
}

// IP y versión de PHP para el footer
$user_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'IP desconocida';
$php_version = phpversion();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>EVE Links Manager</title>

    <!-- Bootstrap 4.6 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            padding-top: 70px;
            padding-bottom: 70px;
            background-color: #111;
            color: #f8f9fa;
        }

        /* Tiles estilo Metro */
        .metro-tile {
            display: block;
            position: relative;
            padding: 20px;
            margin-bottom: 20px;
            color: #fff;
            text-align: left;
            border-radius: 4px;
            text-decoration: none;
            transition: transform 0.1s ease-in-out, box-shadow 0.1s ease-in-out, opacity 0.1s;
            min-height: 110px;
        }

        .metro-tile:hover {
            text-decoration: none;
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            opacity: 0.95;
        }

        .metro-tile i {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .metro-tile-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .metro-tile-delete {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 18px;
            color: rgba(255,255,255,0.7);
            cursor: pointer;
            z-index: 10;
        }

        .metro-tile-delete:hover {
            color: #fff;
        }

        /* Colores Metro */
        .metro-blue { background-color: #0078d7; }
        .metro-green { background-color: #107c10; }
        .metro-orange { background-color: #d83b01; }
        .metro-purple { background-color: #5c2d91; }
        .metro-teal { background-color: #008272; }
        .metro-red { background-color: #e81123; }
        .metro-cyan { background-color: #00b7c3; }
        .metro-lime { background-color: #00cc6a; }
        .metro-magenta { background-color: #e3008c; }
        .metro-brown { background-color: #825a2c; }

        .navbar-brand {
            font-weight: 600;
        }

        .btn-salir {
            color: #ffeb3b !important;
        }

        .footer-fixed {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px 15px;
            background-color: #222;
            color: #ddd;
            font-size: 0.9rem;
            border-top: 1px solid #444;
            z-index: 1030;
        }

        .form-dark {
            background-color: #222;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .form-dark label {
            color: #ddd;
            font-weight: 500;
        }

        .form-dark .form-control,
        .form-dark .form-control:focus {
            background-color: #333;
            border-color: #555;
            color: #fff;
        }

        .category-header {
            background-color: #222;
            padding: 15px;
            border-radius: 8px;
            margin-top: 30px;
            margin-bottom: 20px;
            border-left: 5px solid;
        }

        .table-dark-custom {
            background-color: #222;
        }

        .table-dark-custom th {
            background-color: #333;
        }
    </style>
</head>
<body>

<!-- NAVBAR FIJO -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="?">
        <i class="fas fa-link"></i> EVE Links Manager
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="#mis-links">
                    <i class="fas fa-home"></i> Mis Links
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#crear-categoria">
                    <i class="fas fa-folder-plus"></i> Nueva Categoría
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#crear-link">
                    <i class="fas fa-plus-circle"></i> Nuevo Link
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#administrar">
                    <i class="fas fa-cog"></i> Administrar
                </a>
            </li>
        </ul>

        <form class="form-inline my-2 my-lg-0">
            <a href="?module=logout"
               class="btn btn-danger btn-salir"
               onclick="return confirmarSalida();">
                <i class="fas fa-door-open"></i> Salir
            </a>
        </form>
    </div>
</nav>

<!-- CONTENIDO PRINCIPAL -->
<div class="container-fluid">

    <!-- MENSAJES -->
    <?php if (!empty($mensaje)): ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($mensaje); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- SECCIÓN: MIS LINKS -->
    <div class="row" id="mis-links">
        <div class="col-12 mb-3">
            <h4><i class="fas fa-bookmark"></i> Mis Enlaces Rápidos</h4>
            <p class="text-muted">Haz clic en cualquier mosaico para abrir el enlace en una nueva pestaña.</p>
        </div>
    </div>

    <?php if (empty($links_by_category)): ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                No hay categorías ni enlaces aún. <a href="#crear-categoria" class="alert-link">Crea tu primera categoría</a>.
            </div>
        </div>
    </div>
    <?php else: ?>
        <?php foreach ($links_by_category as $cat_id => $data): ?>
            <?php 
            $cat_info = $data['category_info'];
            $links = $data['links'];
            ?>
            
            <div class="row">
                <div class="col-12">
                    <div class="category-header" style="border-color: <?php 
                        // Extraer color base de la clase metro
                        $color_map = array(
                            'metro-blue' => '#0078d7',
                            'metro-green' => '#107c10',
                            'metro-orange' => '#d83b01',
                            'metro-purple' => '#5c2d91',
                            'metro-teal' => '#008272',
                            'metro-red' => '#e81123',
                            'metro-cyan' => '#00b7c3',
                            'metro-lime' => '#00cc6a',
                            'metro-magenta' => '#e3008c',
                            'metro-brown' => '#825a2c'
                        );
                        echo isset($color_map[$cat_info['category_color']]) ? $color_map[$cat_info['category_color']] : '#0078d7';
                    ?>;">
                        <h5>
                            <i class="fas <?php echo htmlspecialchars($cat_info['category_icon']); ?>"></i>
                            <?php echo htmlspecialchars($cat_info['category_name']); ?>
                            <small class="text-muted">(<?php echo count($links); ?> enlaces)</small>
                        </h5>
                    </div>
                </div>
            </div>

            <?php if (empty($links)): ?>
            <div class="row">
                <div class="col-12">
                    <p class="text-muted ml-3">No hay enlaces en esta categoría.</p>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($links as $lnk): ?>
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <a href="<?php echo htmlspecialchars($lnk['link_url']); ?>"
                       class="metro-tile <?php echo htmlspecialchars($cat_info['category_color']); ?>"
                       target="_blank"
                       title="<?php echo htmlspecialchars($lnk['link_comment']); ?>">
                        <i class="metro-tile-delete fas fa-times"
                           onclick="event.preventDefault(); if(confirm('¿Eliminar este link?')) window.location.href='?delete_link=<?php echo $lnk['link_id']; ?>';"></i>
                        <i class="fas <?php echo htmlspecialchars($cat_info['category_icon']); ?>"></i>
                        <div class="metro-tile-title">
                            <?php echo htmlspecialchars($lnk['link_title']); ?>
                        </div>
                        <?php if (!empty($lnk['link_comment'])): ?>
                        <div>
                            <small><?php echo htmlspecialchars(substr($lnk['link_comment'], 0, 50)); ?><?php echo strlen($lnk['link_comment']) > 50 ? '...' : ''; ?></small>
                        </div>
                        <?php endif; ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        <?php endforeach; ?>
    <?php endif; ?>

    <!-- SECCIÓN: CREAR CATEGORÍA -->
    <div class="row mt-5" id="crear-categoria">
        <div class="col-12 col-lg-6">
            <div class="form-dark">
                <h5><i class="fas fa-folder-plus"></i> Crear Nueva Categoría</h5>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create_category">
                    
                    <div class="form-group">
                        <label for="category_name">Nombre de Categoría *</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>

                    <div class="form-group">
                        <label for="category_icon">Icono Font Awesome *</label>
                        <input type="text" class="form-control" id="category_icon" name="category_icon" 
                               value="fa-link" placeholder="fa-rocket">
                        <small class="text-muted">
                            Usa nombres de <a href="https://fontawesome.com/v5/search?m=free" target="_blank" class="text-info">Font Awesome 5</a> como: fa-rocket, fa-gamepad, fa-server
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="category_color">Color Metro *</label>
                        <select class="form-control" id="category_color" name="category_color">
                            <option value="metro-blue">Azul</option>
                            <option value="metro-green">Verde</option>
                            <option value="metro-orange">Naranja</option>
                            <option value="metro-purple">Morado</option>
                            <option value="metro-teal">Turquesa</option>
                            <option value="metro-red">Rojo</option>
                            <option value="metro-cyan">Cyan</option>
                            <option value="metro-lime">Lima</option>
                            <option value="metro-magenta">Magenta</option>
                            <option value="metro-brown">Café</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Crear Categoría
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- SECCIÓN: CREAR LINK -->
    <div class="row mt-3" id="crear-link">
        <div class="col-12 col-lg-8">
            <div class="form-dark">
                <h5><i class="fas fa-plus-circle"></i> Crear Nuevo Link</h5>
                
                <?php if (empty($categories)): ?>
                <div class="alert alert-warning">
                    Primero debes <a href="#crear-categoria" class="alert-link">crear al menos una categoría</a>.
                </div>
                <?php else: ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create_link">
                    
                    <div class="form-group">
                        <label for="link_category">Categoría *</label>
                        <select class="form-control" id="link_category" name="link_category" required>
                            <option value="">-- Selecciona una categoría --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="link_title">Título del Link *</label>
                        <input type="text" class="form-control" id="link_title" name="link_title" 
                               placeholder="Mi Sitio Favorito" required>
                    </div>

                    <div class="form-group">
                        <label for="link_url">URL *</label>
                        <input type="url" class="form-control" id="link_url" name="link_url" 
                               placeholder="https://ejemplo.com" required>
                    </div>

                    <div class="form-group">
                        <label for="link_comment">Comentario</label>
                        <textarea class="form-control" id="link_comment" name="link_comment" 
                                  rows="2" placeholder="Notas o descripción del enlace..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Link
                    </button>
                </form>
                
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SECCIÓN: ADMINISTRAR -->
    <div class="row mt-5 mb-5" id="administrar">
        <div class="col-12">
            <h5><i class="fas fa-cog"></i> Administrar Categorías</h5>
            
            <?php if (empty($categories)): ?>
            <div class="alert alert-info">No hay categorías para administrar.</div>
            <?php else: ?>
            
            <table class="table table-dark table-bordered table-sm table-dark-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Icono</th>
                        <th>Nombre</th>
                        <th>Color</th>
                        <th>Fecha Creación</th>
                        <th>Enlaces</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?php echo $cat['category_id']; ?></td>
                        <td><i class="fas <?php echo htmlspecialchars($cat['category_icon']); ?>"></i></td>
                        <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                        <td>
                            <span class="badge <?php echo htmlspecialchars($cat['category_color']); ?>" style="padding: 5px 10px;">
                                <?php echo htmlspecialchars($cat['category_color']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($cat['created_at'])); ?></td>
                        <td>
                            <?php 
                            $cat_id = (int)$cat['category_id'];
                            echo isset($links_by_category[$cat_id]) ? count($links_by_category[$cat_id]['links']) : 0; 
                            ?>
                        </td>
                        <td>
                            <a href="?delete_category=<?php echo $cat['category_id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Eliminar esta categoría y TODOS sus enlaces?');">
                                <i class="fas fa-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- FOOTER FIJO -->
<div class="footer-fixed">
    <div class="d-flex justify-content-between">
        <div>
            <i class="fas fa-network-wired"></i>
            IP: <strong><?php echo htmlspecialchars($user_ip); ?></strong>
        </div>
        <div>
            <i class="fab fa-php"></i>
            PHP: <strong><?php echo htmlspecialchars($php_version); ?></strong>
        </div>
    </div>
</div>

<!-- JS de Bootstrap 4.6 -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function confirmarSalida() {
        return confirm('¿Seguro que deseas salir?');
    }
</script>

</body>
</html>
