<?php
require_once __DIR__ . '/../config/conexion.php';

echo "Inspeccionando colección 'productos'...\n";
$prods = $col_productos->find()->toArray();

foreach ($prods as $p) {
    $cat_id = $p['categoria_id'];
    $type = gettype($cat_id);
    if (is_object($cat_id)) { $type .= " (" . get_class($cat_id) . ")"; }
    echo "Producto: " . $p['nombre'] . " | Categoria_ID: " . (string)$cat_id . " | Type: " . $type . "\n";
    
    if (!($cat_id instanceof MongoDB\BSON\ObjectId)) {
        echo "   WARNING: Categoria_ID is NOT an ObjectId!\n";
    }
}

echo "\nInspeccionando colección 'categorias'...\n";
$cats = $col_categorias->find()->toArray();
foreach ($cats as $c) {
    echo "ID: " . $c['_id'] . " | Nombre: " . $c['nombre'] . "\n";
}
