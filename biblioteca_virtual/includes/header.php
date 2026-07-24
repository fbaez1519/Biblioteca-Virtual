<?php
// ============================================
// HEADER - BIBLIOTECA VIRTUAL
// ============================================

// Verificar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
$isAuthenticated = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME ?? 'Biblioteca Virtual'; ?> - <?php echo $pageTitle ?? 'Inicio'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ============================================
           ESTILOS UNIFICADOS - HEADER Y SIDEBAR
           ============================================ */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f5f7fa;
            color: #2d3436;
            min-height: 100vh;
        }
        
        /* ========================================== */
        /* TOP NAVBAR */
        /* ========================================== */
        
        .top-navbar {
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            padding: 0 2rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(0,0,0,0.04);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            color: #2d3436;
        }
        
        .navbar-brand:hover {
            color: #667eea;
        }
        
        .brand-icon {
            font-size: 1.5rem;
        }
        
        .brand-text {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .user-name {
            font-size: 0.9rem;
            font-weight: 500;
            color: #2d3436;
        }
        
        .logout-btn {
            padding: 0.4rem 1rem;
            background: #fde8e8;
            color: #c0392b;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-family: 'Inter', sans-serif;
        }
        
        .logout-btn:hover {
            background: #f5c6cb;
            transform: translateY(-1px);
        }
        
        /* ========================================== */
        /* SIDEBAR */
        /* ========================================== */
        
        .sidebar {
            position: fixed;
            top: 64px;
            left: 0;
            bottom: 0;
            width: 220px;
            background: white;
            border-right: 1px solid #f0f0f0;
            padding: 1.5rem 0;
            overflow-y: auto;
            z-index: 999;
            transition: transform 0.3s ease;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 3px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #f0f0f0;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            padding: 0.2rem 0.8rem;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.6rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            color: #636e72;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu li a:hover {
            background: rgba(102,126,234,0.06);
            color: #667eea;
        }
        
        .sidebar-menu li a.active {
            background: rgba(102,126,234,0.12);
            color: #667eea;
            font-weight: 600;
        }
        
        .sidebar-menu li a .menu-icon {
            font-size: 1.2rem;
            width: 28px;
            text-align: center;
        }
        
        .sidebar-menu .divider {
            height: 1px;
            background: #f0f0f0;
            margin: 0.5rem 1.5rem;
        }
        
        .sidebar-menu .logout-link {
            color: #E17055;
        }
        
        .sidebar-menu .logout-link:hover {
            background: rgba(225,112,85,0.06);
            color: #E17055;
        }
        
        /* ========================================== */
        /* MAIN CONTENT */
        /* ========================================== */
        
        .main-content {
            margin-left: 220px;
            margin-top: 64px;
            padding: 1.5rem 2rem;
            min-height: calc(100vh - 64px);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* ========================================== */
        /* ALERTAS */
        /* ========================================== */
        
        .alert {
            padding: 0.8rem 1.2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.9rem;
            font-weight: 500;
            animation: slideDown 0.4s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: #e8f8ed;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #fde8e8;
            color: #c0392b;
            border: 1px solid #f5c6cb;
        }
        
        .alert-warning {
            background: #fff8e1;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .alert-info {
            background: #e8f0fe;
            color: #004085;
            border: 1px solid #b8d4f0;
        }
        
        /* ========================================== */
        /* BOTONES */
        /* ========================================== */
        
        .btn {
            padding: 0.5rem 1.2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 1px solid #667eea;
        }
        
        .btn-outline:hover {
            background: #667eea;
            color: white;
        }
        
        .btn-success {
            background: #00B894;
            color: white;
        }
        
        .btn-danger {
            background: #E17055;
            color: white;
        }
        
        .btn-sm {
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
        }
        
        /* ========================================== */
        /* RESPONSIVE */
        /* ========================================== */
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 260px;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            /* Hamburguesa */
            .menu-toggle {
                display: flex !important;
            }
        }
        
        @media (min-width: 993px) {
            .menu-toggle {
                display: none !important;
            }
        }
        
        @media (max-width: 768px) {
            .top-navbar {
                padding: 0 1rem;
                height: 56px;
            }
            
            .brand-text {
                font-size: 0.9rem;
            }
            
            .user-name {
                display: none;
            }
            
            .main-content {
                padding: 0.8rem;
                margin-top: 56px;
            }
        }
        
        @media (max-width: 480px) {
            .brand-text {
                font-size: 0.8rem;
            }
            
            .brand-icon {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>

    <!-- ========================================== -->
    <!-- TOP NAVBAR -->
    <!-- ========================================== -->
    <nav class="top-navbar">
        <div style="display:flex;align-items:center;gap:1rem;">
            <button class="menu-toggle" onclick="toggleSidebar()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#2d3436;display:none;">
                ☰
            </button>
            <a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>" class="navbar-brand">
                <span class="brand-icon">📚</span>
                <span class="brand-text">Biblioteca Virtual</span>
            </a>
        </div>
        
        <div class="navbar-user">
            <?php if ($isAuthenticated): ?>
                <span class="user-avatar"><?php echo substr($userName, 0, 1); ?></span>
                <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                <a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>modules/auth/logout.php" class="logout-btn">
                    🚪 Salir
                </a>
            <?php else: ?>
                <a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>modules/auth/login.php" class="btn btn-outline">Iniciar Sesión</a>
                <a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>modules/auth/register.php" class="btn btn-primary">Registrarse</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- ========================================== -->
    <!-- SIDEBAR -->
    <!-- ========================================== -->
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li>
                <a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>" class="<?php echo ($pageTitle ?? '') === 'Inicio' ? 'active' : ''; ?>">
                    <span class="menu-icon">🏠</span> Dashboard
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>modules/resources/index.php" class="<?php echo strpos($pageTitle ?? '', 'Recurso') !== false ? 'active' : ''; ?>">
                    <span class="menu-icon">📚</span> Recursos
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>modules/prestamos/index.php" class="<?php echo strpos($pageTitle ?? '', 'Préstamo') !== false ? 'active' : ''; ?>">
                    <span class="menu-icon">📄</span> Préstamos
                </a>
            </li>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>
                <li>
                    <a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>modules/admin/dashboard.php" class="<?php echo strpos($pageTitle ?? '', 'Administración') !== false ? 'active' : ''; ?>">
                        <span class="menu-icon">👑</span> Administración
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="divider"></li>
            
            <li>
                <a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>modules/profile/index.php">
                    <span class="menu-icon">👤</span> Mi Perfil
                </a>
            </li>
            <li>
                <a href="<?php echo SITE_URL ?? '/biblioteca_virtual/'; ?>modules/auth/logout.php" class="logout-link">
                    <span class="menu-icon">🚪</span> Cerrar Sesión
                </a>
            </li>
        </ul>
    </aside>

    <!-- ========================================== -->
    <!-- OVERLAY PARA MÓVIL -->
    <!-- ========================================== -->
    <div id="sidebarOverlay" onclick="closeSidebar()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.3);z-index:998;"></div>

    <!-- ========================================== -->
    <!-- MAIN CONTENT -->
    <!-- ========================================== -->
    <main class="main-content">
        <div class="container">
        <?php displayAlert(); ?>  

<script>
    // ==========================================
    // FUNCIONES DEL SIDEBAR
    // ==========================================
    
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('open');
        overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
    }
    
    function closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.remove('open');
        overlay.style.display = 'none';
    }
    
    // Cerrar sidebar al hacer clic fuera
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.menu-toggle');
        if (window.innerWidth <= 992) {
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                closeSidebar();
            }
        }
    });
    
    // Cerrar sidebar al redimensionar a desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            closeSidebar();
            document.querySelector('.menu-toggle').style.display = 'none';
        } else {
            document.querySelector('.menu-toggle').style.display = 'flex';
        }
    });
    
    // Mostrar/ocultar botón hamburguesa al cargar
    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth <= 992) {
            document.querySelector('.menu-toggle').style.display = 'flex';
        }
    });
</script>