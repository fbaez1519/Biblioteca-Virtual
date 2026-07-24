<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

global $auth;

// Si ya está autenticado, redirigir al inicio
if ($auth->isAuthenticated()) {
    header('Location: ' . SITE_URL . 'index.php');
    exit;
}

$error = '';
$email = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, ingresa tu email y contraseña';
    } else {
        $result = $auth->login($email, $password);
        if ($result['success']) {
            header('Location: ' . SITE_URL . 'index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ============================================
           ESTILOS - LOGIN EQUILIBRADO
           ============================================ */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        /* ========================================== */
        /* FONDO CON ANIMACIÓN SUTIL */
        /* ========================================== */
        
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }
        
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.08;
            animation: floatShape 25s ease-in-out infinite;
        }
        
        .bg-shape:nth-child(1) {
            width: 350px;
            height: 350px;
            background: #f093fb;
            top: -80px;
            right: -80px;
            animation-delay: 0s;
        }
        
        .bg-shape:nth-child(2) {
            width: 280px;
            height: 280px;
            background: #4facfe;
            bottom: -60px;
            left: -60px;
            animation-delay: -7s;
        }
        
        .bg-shape:nth-child(3) {
            width: 180px;
            height: 180px;
            background: #43e97b;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -14s;
        }
        
        @keyframes floatShape {
            0%, 100% {
                transform: translate(0, 0) scale(1) rotate(0deg);
            }
            33% {
                transform: translate(40px, -30px) scale(1.05) rotate(120deg);
            }
            66% {
                transform: translate(-30px, 40px) scale(0.95) rotate(240deg);
            }
        }
        
        /* ========================================== */
        /* CONTENEDOR PRINCIPAL */
        /* ========================================== */
        
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            animation: fadeUp 0.7s ease-out;
        }
        
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(15px);
            border-radius: 24px;
            padding: 2.5rem 2.2rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25), 
                        0 0 0 1px rgba(255, 255, 255, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 35px 80px rgba(0, 0, 0, 0.3);
        }
        
        /* ========================================== */
        /* HEADER */
        /* ========================================== */
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            line-height: 80px;
            font-size: 2.8rem;
            margin-bottom: 1rem;
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.35);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }
        
        .login-logo:hover {
            transform: scale(1.08) rotate(-5deg);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.5);
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.3rem;
            letter-spacing: -0.5px;
        }
        
        .login-header p {
            color: #7f8c8d;
            font-size: 0.95rem;
            font-weight: 400;
        }
        
        .login-header .badge {
            display: inline-block;
            background: rgba(102, 126, 234, 0.1);
            padding: 0.2rem 1rem;
            border-radius: 50px;
            color: #667eea;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.4rem;
            border: 1px solid rgba(102, 126, 234, 0.15);
        }
        
        /* ========================================== */
        /* ALERTAS */
        /* ========================================== */
        
        .alert {
            padding: 0.8rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.9rem;
            font-weight: 500;
            animation: shake 0.4s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-8px); }
            60% { transform: translateX(8px); }
        }
        
        .alert-error {
            background: #fde8e8;
            color: #c0392b;
            border: 1px solid #f5c6cb;
        }
        
        .alert-icon {
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        /* ========================================== */
        /* FORMULARIO */
        /* ========================================== */
        
        .login-form .form-group {
            margin-bottom: 1.2rem;
        }
        
        .login-form label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 0.4rem;
        }
        
        .login-form .input-wrapper {
            position: relative;
        }
        
        .login-form .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.1rem;
            color: #b2bec3;
            transition: color 0.3s;
            pointer-events: none;
        }
        
        .login-form input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 3rem;
            border: 2px solid #e8ecf1;
            border-radius: 14px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
            background: #f8f9fa;
            color: #2d3436;
        }
        
        .login-form input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .login-form input:focus ~ .input-icon {
            color: #667eea;
        }
        
        .login-form input::placeholder {
            color: #b2bec3;
            font-size: 0.9rem;
        }
        
        /* ========================================== */
        /* BOTÓN */
        /* ========================================== */
        
        .btn-login {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1.05rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 0.3rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.35);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login .btn-icon {
            display: inline-block;
            margin-right: 0.4rem;
            transition: transform 0.3s;
        }
        
        .btn-login:hover .btn-icon {
            transform: translateX(4px);
        }
        
        .btn-login.loading {
            opacity: 0.7;
            cursor: wait;
        }
        
        /* ========================================== */
        /* FOOTER */
        /* ========================================== */
        
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.2rem;
            border-top: 1px solid #f0f0f0;
        }
        
        .login-footer p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            transition: color 0.3s;
        }
        
        .login-footer a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        /* ========================================== */
        /* SEGURIDAD */
        /* ========================================== */
        
        .security-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            margin-top: 1.2rem;
            padding: 0.6rem;
            background: rgba(102, 126, 234, 0.04);
            border-radius: 10px;
            border: 1px solid rgba(102, 126, 234, 0.06);
        }
        
        .security-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: #43e97b;
            border-radius: 50%;
            animation: pulseDot 2s infinite;
        }
        
        @keyframes pulseDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.7); }
        }
        
        .security-bar span {
            font-size: 0.75rem;
            color: #7f8c8d;
            font-weight: 500;
        }
        
        .security-divider {
            color: #dfe6e9;
        }
        
        /* ========================================== */
        /* RESPONSIVE */
        /* ========================================== */
        
        @media (max-width: 768px) {
            .login-card {
                padding: 2rem 1.5rem;
                border-radius: 20px;
            }
            
            .login-logo {
                width: 70px;
                height: 70px;
                line-height: 70px;
                font-size: 2.4rem;
            }
            
            .login-header h1 {
                font-size: 1.6rem;
            }
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 1.5rem 1.2rem;
                border-radius: 16px;
            }
            
            .login-logo {
                width: 60px;
                height: 60px;
                line-height: 60px;
                font-size: 2rem;
                margin-bottom: 0.8rem;
            }
            
            .login-header h1 {
                font-size: 1.4rem;
            }
            
            .login-header p {
                font-size: 0.85rem;
            }
            
            .login-form input {
                font-size: 0.9rem;
                padding: 0.7rem 0.8rem 0.7rem 2.6rem;
            }
            
            .login-form .input-icon {
                font-size: 1rem;
                left: 12px;
            }
            
            .btn-login {
                font-size: 0.95rem;
                padding: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- ========================================== -->
    <!-- FONDO CON FORMAS ANIMADAS -->
    <!-- ========================================== -->
    <div class="bg-shapes">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
    </div>
    
    <!-- ========================================== -->
    <!-- CONTENEDOR PRINCIPAL -->
    <!-- ========================================== -->
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">📚</div>
                <h1><?php echo SITE_NAME; ?></h1>
                <p>Inicia sesión para continuar</p>
                <span class="badge">✦ Acceso seguro ✦</span>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">❌</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($email); ?>" 
                            placeholder="tu@email.com" 
                            required 
                            autofocus
                        >
                        <span class="input-icon">✉️</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="••••••••" 
                            required
                        >
                        <span class="input-icon">🔑</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="btn-icon">🚀</span>
                    Iniciar Sesión
                </button>
                
                <div class="security-bar">
                    <span class="security-dot"></span>
                    <span>🔐 Conexión segura</span>
                    <span class="security-divider">•</span>
                    <span>🛡️ SSL protegido</span>
                </div>
                
                <div class="login-footer">
                    <p>¿No tienes cuenta? <a href="register.php">Regístrate ahora</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <!-- ========================================== -->
    <!-- JAVASCRIPT INTERACTIVO -->
    <!-- ========================================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const btn = document.getElementById('loginBtn');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            // ==========================================
            // EFECTO DE CARGA EN EL BOTÓN
            // ==========================================
            form.addEventListener('submit', function(e) {
                const email = emailInput.value.trim();
                const password = passwordInput.value.trim();
                
                if (!email || !password) {
                    e.preventDefault();
                    return;
                }
                
                btn.classList.add('loading');
                btn.innerHTML = '<span class="btn-icon">⏳</span> Iniciando sesión...';
                btn.disabled = true;
            });
            
            // ==========================================
            // MOVIMIENTO SUTIL EN LAS FORMAS
            // ==========================================
            document.addEventListener('mousemove', function(e) {
                const shapes = document.querySelectorAll('.bg-shape');
                const mouseX = (e.clientX / window.innerWidth - 0.5) * 15;
                const mouseY = (e.clientY / window.innerHeight - 0.5) * 15;
                
                shapes.forEach((shape, index) => {
                    const speed = (index + 1) * 0.2;
                    shape.style.transform = `translate(${mouseX * speed}px, ${mouseY * speed}px)`;
                });
            });
            
            // ==========================================
            // VALIDACIÓN VISUAL EN TIEMPO REAL
            // ==========================================
            emailInput.addEventListener('input', function() {
                if (this.value.length > 0 && !this.value.includes('@')) {
                    this.style.borderColor = '#E17055';
                } else if (this.value.length > 0) {
                    this.style.borderColor = '#00B894';
                } else {
                    this.style.borderColor = '#e8ecf1';
                }
            });
            
            passwordInput.addEventListener('input', function() {
                if (this.value.length > 0 && this.value.length < 6) {
                    this.style.borderColor = '#FDCB6E';
                } else if (this.value.length >= 6) {
                    this.style.borderColor = '#00B894';
                } else {
                    this.style.borderColor = '#e8ecf1';
                }
            });
        });
    </script>
</body>
</html>