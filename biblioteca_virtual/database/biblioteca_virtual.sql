-- ============================================
-- BIBLIOTECA VIRTUAL - SCRIPT DE BASE DE DATOS
-- ============================================
-- Importar este archivo en phpMyAdmin dentro de
-- la base de datos "biblioteca_virtual"
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TABLA: roles
-- ============================================
CREATE TABLE IF NOT EXISTS roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (id_rol, nombre_rol) VALUES
(1, 'admin'),
(2, 'bibliotecario'),
(3, 'usuario');

-- ============================================
-- TABLA: usuarios
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    telefono VARCHAR(20) DEFAULT NULL,
    direccion VARCHAR(255) DEFAULT NULL,
    id_rol INT NOT NULL DEFAULT 3,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME DEFAULT NULL,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NOTA IMPORTANTE:
-- Los usuarios (admin, bibliotecario, usuario normal) NO se insertan aquí,
-- porque las contraseñas deben ir encriptadas con password_hash() de PHP,
-- y ese hash no se puede generar de forma confiable desde SQL puro.
--
-- Para crear tus usuarios de prueba:
-- 1) Regístrate normalmente desde la página de Registro de la app
--    (eso crea un usuario con rol "usuario" y contraseña bien encriptada).
-- 2) Ejecuta UNA VEZ el archivo database/crear_admin.php (Paso 4.2)
--    desde el navegador para crear un admin y un bibliotecario de prueba.
--    Luego BÓRRALO por seguridad.

-- ============================================
-- TABLA: categorias
-- ============================================
CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categorias (nombre_categoria) VALUES
('Ficción'), ('Ciencia'), ('Historia'), ('Tecnología'), ('Infantil');

-- ============================================
-- TABLA: recursos
-- ============================================
CREATE TABLE IF NOT EXISTS recursos (
    id_recurso INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(150) DEFAULT NULL,
    descripcion TEXT DEFAULT NULL,
    tipo_recurso ENUM('libro', 'audio', 'articulo') NOT NULL DEFAULT 'libro',
    id_categoria INT DEFAULT NULL,
    isbn VARCHAR(30) DEFAULT NULL,
    editorial VARCHAR(150) DEFAULT NULL,
    anio_publicacion YEAR DEFAULT NULL,
    cantidad_total INT NOT NULL DEFAULT 1,
    cantidad_disponible INT NOT NULL DEFAULT 1,
    ubicacion VARCHAR(100) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO recursos (titulo, autor, descripcion, tipo_recurso, id_categoria, isbn, editorial, anio_publicacion, cantidad_total, cantidad_disponible, ubicacion) VALUES
('Cien Años de Soledad', 'Gabriel García Márquez', 'Novela clásica del realismo mágico', 'libro', 1, '978-0307474728', 'Sudamericana', 1967, 3, 3, 'Estante A1'),
('Una Breve Historia del Tiempo', 'Stephen Hawking', 'Divulgación científica sobre el universo', 'libro', 2, '978-0553380163', 'Bantam', 1988, 2, 2, 'Estante B2'),
('Clean Code', 'Robert C. Martin', 'Buenas prácticas de programación', 'libro', 4, '978-0132350884', 'Prentice Hall', 2008, 2, 2, 'Estante C3'),
('Introducción a la Historia Dominicana', 'Frank Moya Pons', 'Historia de la República Dominicana', 'libro', 3, '978-9945586387', 'Caribbean Publishers', 2010, 1, 1, 'Estante D1');

-- ============================================
-- TABLA: prestamos
-- ============================================
CREATE TABLE IF NOT EXISTS prestamos (
    id_prestamo INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_recurso INT NOT NULL,
    fecha_prestamo DATETIME NOT NULL,
    fecha_devolucion_esperada DATETIME NOT NULL,
    fecha_devolucion_real DATETIME DEFAULT NULL,
    estado ENUM('activo', 'devuelto', 'vencido', 'cancelado') NOT NULL DEFAULT 'activo',
    observaciones TEXT DEFAULT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_recurso) REFERENCES recursos(id_recurso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;