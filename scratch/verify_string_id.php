<?php
require_once __DIR__ . '/../config/conexion.php';

// 1. Crear categoría
$res = $col_categorias->insertOne(['nombre' => 'TEST_STRING_ID', 'imagen' => '']);
$cat_id = (string)$res->getInsertedId();
echo "Categoría creada: $cat_id\n";

// 2. Crear producto con categoria_id como STRING (inconsistente)
$col_productos->insertOne([
    'nombre' => 'Producto Fantasma',
    'categoria_id' => $cat_id // Guardado como string!
]);
echo "Producto con ID string creado.\n";

// 3. Simular lógica nueva de categorias.php
$oid = new MongoDB\BSON\ObjectId($cat_id);
$count = $col_productos->countDocuments([
    '$or' => [
        ['categoria_id' => $oid],
        ['categoria_id' => $cat_id]
    ]
]);

echo "Conteo de productos (usando lógica nueva): $count\n";

if ($count > 0) {
    echo "ÉXITO: La lógica nueva detectó el producto con ID string.\n";
} else {
    echo "FALLO: La lógica nueva NO detectó el producto con ID string.\n";
}

// Limpiar
$col_productos->deleteOne(['categoria_id' => $cat_id]);
$col_categorias->deleteOne(['_id' => $oid]);
echo "Limpieza completada.\n";
