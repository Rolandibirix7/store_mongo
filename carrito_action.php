<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/conexion.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

switch ($action) {

    case 'agregar':
        $id       = trim($_POST['producto_id'] ?? '');
        $cantidad = max(1, (int)($_POST['cantidad'] ?? 1));
        try {
            $oid  = new MongoDB\BSON\ObjectId($id);
            $prod = $col_productos->findOne(['_id' => $oid]);
            if (!$prod) { echo json_encode(['ok'=>false,'msg'=>'Producto no existe']); exit; }

            if (isset($_SESSION['carrito'][$id])) {
                $_SESSION['carrito'][$id]['cantidad'] += $cantidad;
            } else {
                $_SESSION['carrito'][$id] = [
                    'id'       => $id,
                    'nombre'   => (string)$prod['nombre'],
                    'precio'   => (float)$prod['precio'],
                    'imagen'   => (string)$prod['imagen'],
                    'cantidad' => $cantidad,
                ];
            }
            echo json_encode(['ok'=>true,'total_items'=>array_sum(array_column($_SESSION['carrito'],'cantidad'))]);
        } catch(Exception $e) {
            echo json_encode(['ok'=>false,'msg'=>'ID inválido']);
        }
        break;

    case 'eliminar':
        $id = trim($_POST['producto_id'] ?? '');
        unset($_SESSION['carrito'][$id]);
        echo json_encode(['ok'=>true]);
        break;

    case 'actualizar':
        $id       = trim($_POST['producto_id'] ?? '');
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        if ($cantidad <= 0) {
            unset($_SESSION['carrito'][$id]);
        } elseif (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]['cantidad'] = $cantidad;
        }
        echo json_encode(['ok'=>true]);
        break;

    case 'count':
        $total = array_sum(array_column($_SESSION['carrito'] ?? [], 'cantidad'));
        echo json_encode(['count'=>$total]);
        break;

    default:
        echo json_encode(['ok'=>false,'msg'=>'Acción no reconocida']);
}
