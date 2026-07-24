<?php
// ============================================
// SISTEMA DE AUTENTICACIÓN
// ============================================

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    private $userData = null;
    
    public function __construct() {
        $this->db = getDB();
        
        // Verificar sesión al inicializar
        if (isset($_SESSION['user_id'])) {
            $this->loadUserData($_SESSION['user_id']);
        }
    }
    
    // Cargar datos del usuario
    private function loadUserData($userId) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.nombre_rol 
            FROM usuarios u 
            JOIN roles r ON u.id_rol = r.id_rol 
            WHERE u.id_usuario = ? AND u.activo = 1
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $this->userData = $result->fetch_assoc();
            return true;
        }
        
        $this->logout();
        return false;
    }
    
    // Iniciar sesión
    public function login($email, $password) {
        // Buscar usuario por email
        $stmt = $this->db->prepare("
            SELECT * FROM usuarios WHERE email = ? AND activo = 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        $user = $result->fetch_assoc();
        
        // Verificar contraseña
        if (!password_verify($password, $user['contrasena'])) {
            return ['success' => false, 'message' => 'Contraseña incorrecta'];
        }
        
        // Actualizar último acceso
        $updateStmt = $this->db->prepare("
            UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?
        ");
        $updateStmt->bind_param("i", $user['id_usuario']);
        $updateStmt->execute();
        
        // Crear sesión
        $_SESSION['user_id'] = $user['id_usuario'];
        $_SESSION['user_name'] = $user['nombre_completo'];
        $_SESSION['user_role'] = $user['id_rol'];
        $_SESSION['login_time'] = time();
        
        // Cargar datos del usuario
        $this->loadUserData($user['id_usuario']);
        
        return ['success' => true, 'message' => 'Bienvenido ' . $user['nombre_completo']];
    }
    
    // Cerrar sesión
    public function logout() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        $this->userData = null;
    }
    
    // Verificar si está autenticado
    public function isAuthenticated() {
        return $this->userData !== null;
    }
    
    // Verificar si tiene un rol específico
    public function hasRole($roleName) {
        if (!$this->isAuthenticated()) return false;
        return $this->userData['nombre_rol'] === $roleName;
    }
    
    // Verificar si es administrador
    public function isAdmin() {
        return $this->hasRole('admin');
    }
    
    // Verificar si es bibliotecario
    public function isBibliotecario() {
        return $this->hasRole('bibliotecario') || $this->isAdmin();
    }
    
    // Obtener datos del usuario autenticado
    public function getUser() {
        return $this->userData;
    }
    
    // Obtener ID del usuario
    public function getUserId() {
        return $this->isAuthenticated() ? $this->userData['id_usuario'] : null;
    }
    
    // Registrar nuevo usuario
    public function register($data) {
        // Validar datos
        $errors = $this->validateRegistration($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Hash de la contraseña
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insertar usuario
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (nombre_usuario, email, contrasena, nombre_completo, telefono, direccion) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "ssssss",
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['fullname'],
            $data['phone'],
            $data['address']
        );
        
        try {
            $stmt->execute();
            return ['success' => true, 'message' => 'Usuario registrado exitosamente'];
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return ['success' => false, 'message' => 'El email o nombre de usuario ya está registrado'];
            }
            return ['success' => false, 'message' => 'Error al registrar usuario: ' . $e->getMessage()];
        }
    }
    
    // Validar datos de registro
    private function validateRegistration($data) {
        $errors = [];
        
        if (empty($data['username'])) {
            $errors['username'] = 'El nombre de usuario es requerido';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'El nombre de usuario debe tener al menos 3 caracteres';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El email no es válido';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'La contraseña es requerida';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Las contraseñas no coinciden';
        }
        
        if (empty($data['fullname'])) {
            $errors['fullname'] = 'El nombre completo es requerido';
        }
        
        return $errors;
    }
    
    // Verificar timeout de sesión
    public function checkSessionTimeout() {
        if ($this->isAuthenticated() && isset($_SESSION['login_time'])) {
            $timeout = SESSION_TIMEOUT;
            if (time() - $_SESSION['login_time'] > $timeout) {
                $this->logout();
                return false;
            }
            // Renovar tiempo de sesión
            $_SESSION['login_time'] = time();
            return true;
        }
        return true;
    }
}

// Crear instancia global de Auth
$auth = new Auth();

// Función helper para verificar autenticación
function requireLogin() {
    global $auth;
    if (!$auth->isAuthenticated()) {
        header('Location: ' . SITE_URL . 'modules/auth/login.php');
        exit;
    }
    $auth->checkSessionTimeout();
}

// Función helper para verificar administrador
function requireAdmin() {
    global $auth;
    requireLogin();
    if (!$auth->isAdmin()) {
        header('Location: ' . SITE_URL . 'index.php');
        exit;
    }
}

// Función helper para verificar bibliotecario
function requireBibliotecario() {
    global $auth;
    requireLogin();
    if (!$auth->isBibliotecario()) {
        header('Location: ' . SITE_URL . 'index.php');
        exit;
    }
}
?>