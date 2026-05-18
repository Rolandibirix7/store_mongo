<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $mongoClient = new MongoDB\Client("mongodb+srv://rolanlopez1304_db_user:ikc7SbElwUJIGp8T@cluster0.nptlrpe.mongodb.net/?appName=Cluster0");
    $db = $mongoClient->selectDatabase("dbstore");

    $col_categorias = $db->selectCollection("categorias");
    $col_productos   = $db->selectCollection("productos");
    $col_usuarios    = $db->selectCollection("usuarios");
    $col_pedidos     = $db->selectCollection("pedidos");
    $col_tarjetas    = $db->selectCollection("tarjetas");

} catch (Exception $e) {
    die("Error de conexión a MongoDB: " . $e->getMessage());
}
