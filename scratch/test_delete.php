<?php
require_once __DIR__ . '/../config/conexion.php';

// Crear una categoría de prueba
$res = $col_categorias->insertOne(['nombre' => 'PRUEBA_ELIMINAR', 'imagen' => '']);
$id = (string)$res->getInsertedId();
echo "Categoría de prueba creada con ID: $id\n";

// Intentar encontrarla
$cat = $col_categorias->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
if ($cat) {
    echo "Categoría encontrada.\n";
} else {
    echo "ERROR: Categoría no encontrada tras creación.\n";
    exit;
}

// Simular la lógica de categorias.php para eliminar
$oid = new MongoDB\BSON\ObjectId($id);
$enUso = $col_productos->findOne(['categoria_id' => $oid]);
if ($enUso) {
    echo "ERROR: La categoría de prueba dice estar en uso!\n";
} else {
    $delRes = $col_categorias->deleteOne(['_id' => $oid]);
    if ($delRes->getDeletedCount() > 0) {
        echo "ÉXITO: Categoría eliminada correctamente.\n";
    } else {
        echo "ERROR: deleteOne no eliminó nada.\n";
    }
}
