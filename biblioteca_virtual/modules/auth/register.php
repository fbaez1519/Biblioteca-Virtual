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
$success = '';
$formData = [];

// Procesar registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'fullname' => $_POST['fullname'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? ''
    ];
    
    $result = $auth->register($formData);
    
    if ($result['success']) {
        $success = $result['message'];
        // Limpiar datos del formulario
        $formData = [];
    } else {
        $error = isset($result['message']) ? $result['message'] : 'Error al registrar usuario';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ============================================
           ESTILOS - REGISTRO EQUILIBRADO
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
        
        .register-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
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
        
        .register-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(15px);
            border-radius: 24px;
            padding: 2.5rem 2.2rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25), 
                        0 0 0 1px rgba(255, 255, 255, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .register-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 35px 80px rgba(0, 0, 0, 0.3);
        }
        
        /* Scroll personalizado */
        .register-card::-webkit-scrollbar {
            width: 4px;
        }
        
        .register-card::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 10px;
        }
        
        .register-card::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }
        
        /* ========================================== */
        /* HEADER */
        /* ========================================== */
        
        .register-header {
            text-align: center;
            margin-bottom: 1.8rem;
        }
        
        .register-logo {
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
        
        .register-logo:hover {
            transform: scale(1.08) rotate(-5deg);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.5);
        }
        
        .register-header h1 {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.3rem;
            letter-spacing: -0.5px;
        }
        
        .register-header p {
            color: #7f8c8d;
            font-size: 0.95rem;
            font-weight: 400;
        }
        
        .register-header .badge {
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
        
        .alert-success {
            background: #e8f8ed;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-icon {
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .alert a {
            color: #667eea;
            font-weight: 700;
            text-decoration: none;
        }
        
        .alert a:hover {
            text-decoration: underline;
        }
        
        /* ========================================== */
        /* FORMULARIO */
        /* ========================================== */
        
        .register-form .form-group {
            margin-bottom: 1rem;
        }
        
        .register-form label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 0.3rem;
        }
        
        .register-form .input-wrapper {
            position: relative;
        }
        
        .register-form .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            color: #b2bec3;
            transition: color 0.3s;
            pointer-events: none;
        }
        
        .register-form input,
        .register-form textarea {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.8rem;
            border: 2px solid #e8ecf1;
            border-radius: 12px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
            background: #f8f9fa;
            color: #2d3436;
        }
        
        .register-form textarea {
            padding-left: 2.8rem;
            resize: vertical;
            min-height: 60px;
        }
        
        .register-form input:focus,
        .register-form textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .register-form input:focus ~ .input-icon,
        .register-form textarea:focus ~ .input-icon {
            color: #667eea;
        }
        
        .register-form input::placeholder,
        .register-form textarea::placeholder {
            color: #b2bec3;
            font-size: 0.85rem;
        }
        
        .register-form .form-hint {
            display: block;
            font-size: 0.75rem;
            color: #b2bec3;
            margin-top: 0.2rem;
        }
        
        /* ========================================== */
        /* BOTÓN */
        /* ========================================== */
        
        .btn-register {
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
            margin-top: 0.5rem;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.35);
        }
        
        .btn-register:active {
            transform: translateY(0);
        }
        
        .btn-register .btn-icon {
            display: inline-block;
            margin-right: 0.4rem;
            transition: transform 0.3s;
        }
        
        .btn-register:hover .btn-icon {
            transform: translateX(4px);
        }
        
        .btn-register.loading {
            opacity: 0.7;
            cursor: wait;
        }
        
        /* ========================================== */
        /* TÉRMINOS */
        /* ========================================== */
        
        .terms-group {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            margin: 1rem 0 0.5rem;
        }
        
        .terms-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-top: 2px;
            accent-color: #667eea;
            cursor: pointer;
            flex-shrink: 0;
        }
        
        .terms-group label {
            font-size: 0.8rem;
            color: #7f8c8d;
            cursor: pointer;
            font-weight: 400;
        }
        
        .terms-group label a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .terms-group label a:hover {
            text-decoration: underline;
        }
        
        /* ========================================== */
        /* FOOTER */
        /* ========================================== */
        
        .register-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.2rem;
            border-top: 1px solid #f0f0f0;
        }
        
        .register-footer p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .register-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            transition: color 0.3s;
        }
        
        .register-footer a:hover {
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
            .register-card {
                padding: 2rem 1.5rem;
                border-radius: 20px;
            }
            
            .register-logo {
                width: 70px;
                height: 70px;
                line-height: 70px;
                font-size: 2.4rem;
            }
            
            .register-header h1 {
                font-size: 1.6rem;
            }
        }
        
        @media (max-width: 480px) {
            .register-card {
                padding: 1.5rem 1.2rem;
                border-radius: 16px;
            }
            
            .register-logo {
                width: 60px;
                height: 60px;
                line-height: 60px;
                font-size: 2rem;
                margin-bottom: 0.8rem;
            }
            
            .register-header h1 {
                font-size: 1.4rem;
            }
            
            .register-header p {
                font-size: 0.85rem;
            }
            
            .register-form input,
            .register-form textarea {
                font-size: 0.85rem;
                padding: 0.6rem 0.8rem 0.6rem 2.4rem;
            }
            
            .register-form .input-icon {
                font-size: 0.9rem;
                left: 10px;
            }
            
            .btn-register {
                font-size: 0.95rem;
                padding: 0.8rem;
            }
            
            .terms-group label {
                font-size: 0.75rem;
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
    <div class="register-wrapper">
        <div class="register-card">
            <div class="register-header">
                <div class="register-logo">📚</div>
                <h1>Crear Cuenta</h1>
                <p>Únete a nuestra comunidad</p>
                <span class="badge">✦ Registro seguro ✦</span>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">❌</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">✅</span>
                    <span>
                        <?php echo htmlspecialchars($success); ?>
                        <br>
                        <a href="login.php">Iniciar sesión ahora</a>
                    </span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="register-form" id="registerForm">
                <!-- Nombre de Usuario -->
                <div class="form-group">
                    <label for="username">Nombre de Usuario</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>"
                            placeholder="Elige un nombre de usuario"
                            required
                            minlength="3"
                        >
                        <span class="input-icon">👤</span>
                    </div>
                </div>
                
                <!-- Email -->
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                            placeholder="tu@email.com"
                            required
                        >
                        <span class="input-icon">✉️</span>
                    </div>
                </div>
                
                <!-- Nombre Completo -->
                <div class="form-group">
                    <label for="fullname">Nombre Completo</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="fullname" 
                            name="fullname" 
                            value="<?php echo htmlspecialchars($formData['fullname'] ?? ''); ?>"
                            placeholder="Tu nombre completo"
                            required
                        >
                        <span class="input-icon">📝</span>
                    </div>
                </div>
                
                <!-- Teléfono -->
                <div class="form-group">
                    <label for="phone">Teléfono</label>
                    <div class="input-wrapper">
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>"
                            placeholder="809-555-0000"
                        >
                        <span class="input-icon">📱</span>
                    </div>
                </div>
                
                <!-- Dirección -->
                <div class="form-group">
                    <label for="address">Dirección</label>
                    <div class="input-wrapper">
                        <textarea 
                            id="address" 
                            name="address" 
                            placeholder="Tu dirección (opcional)"
                            rows="2"
                        ><?php echo htmlspecialchars($formData['address'] ?? ''); ?></textarea>
                        <span class="input-icon">📍</span>
                    </div>
                </div>
                
                <!-- Contraseña -->
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Mínimo 6 caracteres"
                            required
                            minlength="6"
                        >
                        <span class="input-icon">🔒</span>
                    </div>
                    <span class="form-hint">💡 La contraseña debe tener al menos 6 caracteres</span>
                </div>
                
                <!-- Confirmar Contraseña -->
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Repite tu contraseña"
                            required
                        >
                        <span class="input-icon">✅</span>
                    </div>
                </div>
                
                <!-- Términos y condiciones -->
                <div class="terms-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        Acepto los 
                        <a href="#" onclick="alert('📋 Términos y condiciones del servicio'); return false;">
                            Términos y Condiciones
                        </a> 
                        y la 
                        <a href="#" onclick="alert('🔒 Política de privacidad'); return false;">
                            Política de Privacidad
                        </a>
                    </label>
                </div>
                
                <!-- Botón de registro -->
                <button type="submit" class="btn-register" id="submitBtn">
                    <span class="btn-icon">🚀</span>
                    Crear Cuenta
                </button>
                
                <div class="security-bar">
                    <span class="security-dot"></span>
                    <span>🔐 Datos seguros</span>
                    <span class="security-divider">•</span>
                    <span>🛡️ Encriptación SSL</span>
                </div>
                
                <div class="register-footer">
                    <p>¿Ya tienes cuenta? <a href="login.php">Iniciar Sesión</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <!-- ========================================== -->
    <!-- JAVASCRIPT INTERACTIVO -->
    <!-- ========================================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const btn = document.getElementById('submitBtn');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // ==========================================
            // VALIDACIÓN EN TIEMPO REAL
            // ==========================================
            
            // Validar coincidencia de contraseñas
            function validatePasswordMatch() {
                const errorEl = document.getElementById('confirm-error');
                if (password.value && confirmPassword.value && password.value !== confirmPassword.value) {
                    confirmPassword.style.borderColor = '#E17055';
                    if (!errorEl) {
                        const msg = document.createElement('span');
                        msg.id = 'confirm-error';
                        msg.className = 'form-hint';
                        msg.style.color = '#E17055';
                        msg.textContent = '❌ Las contraseñas no coinciden';
                        confirmPassword.parentElement.parentElement.appendChild(msg);
                    }
                    return false;
                } else {
                    if (errorEl) errorEl.remove();
                    if (confirmPassword.value && password.value === confirmPassword.value) {
                        confirmPassword.style.borderColor = '#00B894';
                    } else {
                        confirmPassword.style.borderColor = '#e8ecf1';
                    }
                    return true;
                }
            }
            
            password.addEventListener('input', function() {
                // Validar longitud
                if (this.value.length > 0 && this.value.length < 6) {
                    this.style.borderColor = '#FDCB6E';
                } else if (this.value.length >= 6) {
                    this.style.borderColor = '#00B894';
                } else {
                    this.style.borderColor = '#e8ecf1';
                }
                
                if (confirmPassword.value) {
                    validatePasswordMatch();
                }
            });
            
            confirmPassword.addEventListener('input', validatePasswordMatch);
            
            // ==========================================
            // VALIDACIÓN DE EMAIL
            // ==========================================
            const emailInput = document.getElementById('email');
            emailInput.addEventListener('input', function() {
                if (this.value.length > 0 && !this.value.includes('@')) {
                    this.style.borderColor = '#FDCB6E';
                } else if (this.value.length > 0 && this.value.includes('@')) {
                    this.style.borderColor = '#00B894';
                } else {
                    this.style.borderColor = '#e8ecf1';
                }
            });
            
            // ==========================================
            // EFECTO DE CARGA EN EL BOTÓN
            // ==========================================
            form.addEventListener('submit', function(e) {
                const terms = document.getElementById('terms');
                if (!terms.checked) {
                    e.preventDefault();
                    alert('⚠️ Debes aceptar los Términos y Condiciones');
                    terms.focus();
                    return;
                }
                
                // Validar contraseñas
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('❌ Las contraseñas no coinciden');
                    confirmPassword.focus();
                    return;
                }
                
                btn.classList.add('loading');
                btn.innerHTML = '<span class="btn-icon">⏳</span> Registrando...';
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
        });
    </script>
</body>
</html>