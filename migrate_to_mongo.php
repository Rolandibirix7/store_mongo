<?php
/**
 * ============================================================
 *  Script de Migración: MySQL → MongoDB
 *  Ejecutar UNA sola vez desde CLI:
 *      php migrate_to_mongo.php
 * ============================================================
 *  Requisitos previos:
 *    1. composer install  (instala mongodb/mongodb)
 *    2. pecl install mongodb  +  habilitar extension=mongodb en php.ini
 *    3. MySQL corriendo con la base de datos 'dbstore'
 *    4. MongoDB corriendo en localhost:27017
 * ============================================================
 */

require_once __DIR__ . '/vendor/autoload.php';

// ── Conexión MySQL ──────────────────────────────────────────
$mysql = new mysqli("localhost", "root", "", "dbstore");
if ($mysql->connect_error) {
    die("MySQL error: " . $mysql->connect_error . "\n");
}
$mysql->set_charset("utf8mb4");

// ── Conexión MongoDB ────────────────────────────────────────
$mongo = new MongoDB\Client("mongodb://localhost:27017");
$db    = $mongo->selectDatabase("dbstore");

// Limpiar colecciones antes de importar (evita duplicados)
$db->categorias->drop();
$db->productos->drop();
$db->usuarios->drop();

echo "=== Iniciando migración ===\n\n";

// ── 1. Categorías ───────────────────────────────────────────
$cat_map   = [];  // mysql_id → MongoDB ObjectId
$resultado = $mysql->query("SELECT * FROM categorias");

while ($row = $resultado->fetch_assoc()) {
    $res = $db->categorias->insertOne([
        'nombre' => $row['nombre'],
        'imagen' => $row['imagen'],
    ]);
    $cat_map[$row['id']] = $res->getInsertedId();
}
echo "✓ Categorías migradas: " . count($cat_map) . "\n";

// ── 2. Productos ────────────────────────────────────────────
$resultado = $mysql->query("SELECT * FROM productos");
$count     = 0;

while ($row = $resultado->fetch_assoc()) {
    $cat_oid = $cat_map[$row['categoria_id']] ?? null;

    $db->productos->insertOne([
        'nombre'       => $row['nombre'],
        'descripcion'  => $row['descripcion'],
        'precio'       => (float)$row['precio'],
        'imagen'       => $row['imagen'],
        'categoria_id' => $cat_oid,
    ]);
    $count++;
}
echo "✓ Productos migrados: $count\n";

// ── 3. Usuarios ─────────────────────────────────────────────
$resultado = $mysql->query("SELECT * FROM usuarios");
$count     = 0;

while ($row = $resultado->fetch_assoc()) {
    $db->usuarios->insertOne([
        'usuario'  => $row['usuario'],
        'password' => $row['password'],   // md5 original preservado
        'rol'      => $row['rol'] ?: 'cliente',
    ]);
    $count++;
}
echo "✓ Usuarios migrados: $count\n";

// ── Índices ─────────────────────────────────────────────────
$db->usuarios->createIndex(['usuario' => 1], ['unique' => true]);
$db->productos->createIndex(['categoria_id' => 1]);
$db->productos->createIndex(['nombre' => 'text', 'descripcion' => 'text']);

echo "\n✓ Índices creados en MongoDB\n";
echo "\n=== Migración completada con éxito ===\n";
