<?php
require_once dirname(__FILE__) . '/config.php';

/**
 * Conexión PDO única. En modo demo (sin DB_DSN) crea la base SQLite con datos de ejemplo.
 */
function db()
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $opciones = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    );

    if (DB_DSN !== '') {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $opciones);
        return $pdo;
    }

    $archivo = dirname(dirname(__FILE__)) . '/data/demo.sqlite';
    $nueva = !file_exists($archivo);
    $pdo = new PDO('sqlite:' . $archivo, null, null, $opciones);
    $pdo->exec('PRAGMA foreign_keys = ON');
    if ($nueva) {
        crear_base_demo($pdo);
    }
    return $pdo;
}

function crear_base_demo(PDO $pdo)
{
    $pdo->exec('CREATE TABLE usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        creado_en DATETIME NOT NULL
    )');
    $pdo->exec('CREATE TABLE clientes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        razon_social VARCHAR(150) NOT NULL,
        cuit VARCHAR(13) UNIQUE,
        email VARCHAR(150),
        telefono VARCHAR(30),
        ciudad VARCHAR(80),
        estado VARCHAR(10) NOT NULL DEFAULT \'activo\',
        notas TEXT,
        creado_por INTEGER REFERENCES usuarios(id) ON DELETE SET NULL,
        creado_en DATETIME NOT NULL,
        actualizado_en DATETIME
    )');

    $ahora = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare('INSERT INTO usuarios (usuario, password_hash, nombre, creado_en) VALUES (?, ?, ?, ?)');
    $stmt->execute(array('demo', password_hash('demo1234', PASSWORD_DEFAULT), 'Usuario Demo', $ahora));

    $ejemplos = array(
        array('Ferretería El Tornillo SRL', '30-71234567-1', 'compras@eltornillo.com.ar', '11 4455-1200', 'Morón', 'activo'),
        array('Distribuidora Norte SA', '30-70987654-2', 'admin@distnorte.com.ar', '11 4789-3300', 'San Isidro', 'activo'),
        array('Panadería La Espiga', '20-28765432-5', 'laespiga@gmail.com', '11 3456-7788', 'Castelar', 'activo'),
        array('Estudio Contable Ríos & Asoc.', '30-71555444-1', 'contacto@riosasoc.com.ar', '11 5263-1111', 'CABA', 'activo'),
        array('Taller Mecánico Don Pepe', '20-17654321-4', 'donpepe.taller@hotmail.com', '11 4627-9090', 'Haedo', 'inactivo'),
        array('Librería Del Centro', '27-30111222-5', 'libreriadelcentro@gmail.com', '11 4629-5050', 'Ituzaingó', 'activo'),
        array('Transportes Oeste SRL', '30-70333222-2', 'logistica@transoeste.com.ar', '11 4489-7700', 'Merlo', 'activo'),
        array('Farmacia Rivadavia', '30-71888999-1', 'farmariva@gmail.com', '11 4658-2323', 'Ramos Mejía', 'activo'),
        array('Kiosco 24hs Luna', '20-33444555-1', 'kioscoluna@gmail.com', '11 2233-4455', 'Morón', 'inactivo'),
        array('Constructora Andes SA', '30-70666777-2', 'obras@andes.com.ar', '11 4312-8800', 'CABA', 'activo'),
        array('Veterinaria Patitas', '27-29888777-6', 'patitasvet@gmail.com', '11 4697-1414', 'Castelar', 'activo'),
        array('Imprenta Rápida Oeste', '30-71222111-5', 'ventas@imprentaoeste.com.ar', '11 4621-6060', 'Morón', 'activo'),
    );
    $stmt = $pdo->prepare('INSERT INTO clientes (razon_social, cuit, email, telefono, ciudad, estado, creado_por, creado_en)
                           VALUES (?, ?, ?, ?, ?, ?, 1, ?)');
    foreach ($ejemplos as $c) {
        $c[] = $ahora;
        $stmt->execute($c);
    }
}
