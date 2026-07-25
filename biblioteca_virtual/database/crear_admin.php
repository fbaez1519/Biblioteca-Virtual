<?php
// ============================================
// CREAR USUARIOS DE PRUEBA (ADMIN Y BIBLIOTECARIO)
// ============================================
// Ejecuta este archivo UNA SOLA VEZ desde el navegador.
// Después de usarlo, BÓRRALO por seguridad.
// ============================================

require_once __DIR__ . '/../config/database.php';

$db = getDB();

$usuariosPrueba = [
    [
        'nombre_usuario' => 'admin',
        'email' => 'admin@biblioteca.com',
        'password' => 'admin123',
        'nombre_completo' => 'Administrador General',
        'telefono' => '000-000-0000',
        'direccion' => 'Oficina Central',
        'id_rol' => 1 // admin
    ],
    [
        'nombre_usuario' => 'bibliotecario',
        'email' => 'biblio@biblioteca.com',
        'password' => 'biblio123',
        'nombre_completo' => 'María Bibliotecaria',
        'telefono' => '111-111-1111',
        'direccion' => 'Sala de Préstamos',
        'id_rol' => 2 // bibliotecario
    ],
];

echo "<h2>Creando usuarios de prueba...</h2><ul>";

foreach ($usuariosPrueba as $u) {
    $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $u['email']);
    $stmt->execute();
    $existe = $stmt->get_result()->fetch_assoc();

    if ($existe) {
        echo "<li>⚠️ {$u['email']} ya existe, se omite.</li>";
        continue;
    }

    $hash = password_hash($u['password'], PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        INSERT INTO usuarios (nombre_usuario, email, contrasena, nombre_completo, telefono, direccion, id_rol)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssssssi",
        $u['nombre_usuario'],
        $u['email'],
        $hash,
        $u['nombre_completo'],
        $u['telefono'],
        $u['direccion'],
        $u['id_rol']
    );
    $stmt->execute();

    echo "<li>✅ Creado: {$u['email']} — contraseña: {$u['password']}</li>";
}

echo "</ul><p><strong>Ahora borra este archivo (crear_admin.php) por seguridad.</strong></p>";
?>