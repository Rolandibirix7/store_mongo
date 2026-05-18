<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['ok'=>false,'msg'=>'No autenticado']); exit;
}

$carrito = $_SESSION['carrito'] ?? [];
if (empty($carrito)) {
    echo json_encode(['ok'=>false,'msg'=>'Carrito vacío']); exit;
}

$user_id   = $_SESSION['user_id'];
$direccion   = trim($_POST['direccion'] ?? '');
$ciudad      = trim($_POST['ciudad'] ?? '');
$departamento = trim($_POST['departamento'] ?? '');
$card_raw  = $_POST['card_data'] ?? '{}';
$card_data = json_decode($card_raw, true);

// ── Validate card ────────────────────────────────────────────
$tarjeta_info = [];

if (!empty($card_data['tarjeta_id'])) {
    // Using a saved card
    try {
        $toid = new MongoDB\BSON\ObjectId($card_data['tarjeta_id']);
        $t    = $col_tarjetas->findOne(['_id' => $toid, 'usuario_id' => $user_id]);
        if (!$t) { echo json_encode(['ok'=>false,'msg'=>'Tarjeta no encontrada']); exit; }
        $num   = (string)$t['numero'];
        $last4 = substr(str_replace(' ','',$num), -4);
        $tarjeta_info = [
            'tipo'  => (string)$t['tipo'],
            'last4' => $last4,
        ];
    } catch(Exception $e) {
        echo json_encode(['ok'=>false,'msg'=>'Tarjeta inválida']); exit;
    }
} else {
    // New card
    $num    = preg_replace('/\D/','',$card_data['numero'] ?? '');
    $exp    = $card_data['vencimiento'] ?? '';
    $cvv    = $card_data['cvv'] ?? '';
    $holder = trim($card_data['titular'] ?? '');
    $tipo   = $card_data['tipo'] ?? 'VISA';
    $save   = !empty($card_data['guardar']);

    if (strlen($num) < 16 || !$exp || strlen($cvv) < 3 || !$holder) {
        echo json_encode(['ok'=>false,'msg'=>'Datos de tarjeta incompletos']); exit;
    }

    // Simulate: reject cards starting with 0000
    if (substr($num,0,4) === '0000') {
        echo json_encode(['ok'=>false,'msg'=>'Tarjeta rechazada por el banco emisor.']); exit;
    }

    $last4 = substr($num,-4);
    $tarjeta_info = ['tipo'=>$tipo,'last4'=>$last4];

    // Format number for storage
    $formatted = implode(' ', str_split($num, 4));

    if ($save) {
        $col_tarjetas->insertOne([
            'usuario_id'  => $user_id,
            'numero'      => $formatted,
            'vencimiento' => $exp,
            'titular'     => $holder,
            'tipo'        => $tipo,
            'creada_en'   => new MongoDB\BSON\UTCDateTime(),
        ]);
    }
}

// ── Build order ─────────────────────────────────────────────
$subtotal = 0;
$items    = [];
foreach ($carrito as $id => $item) {
    $linea     = $item['precio'] * $item['cantidad'];
    $subtotal += $linea;
    $items[]   = [
        'producto_id' => $id,
        'nombre'      => $item['nombre'],
        'precio'      => $item['precio'],
        'cantidad'    => $item['cantidad'],
        'subtotal'    => $linea,
    ];
}
$envio = 3.50;
$total = $subtotal + $envio;

// Generate short readable ID
$pedido_ref = strtoupper(substr(md5(uniqid()),0,8));

$estados = ['Procesando','Confirmado','Enviado','Entregado'];
$estado  = 'Procesando';

$result = $col_pedidos->insertOne([
    'referencia'  => $pedido_ref,
    'usuario_id'  => $user_id,
    'usuario'     => $_SESSION['usuario'],
    'items'       => $items,
    'subtotal'    => $subtotal,
    'envio'       => $envio,
    'total'       => $total,
    'direccion'    => $direccion,
    'departamento' => $departamento,
    'tarjeta'     => $tarjeta_info,
    'estado'      => $estado,
    'fecha'       => new MongoDB\BSON\UTCDateTime(),
]);

// Clear cart
$_SESSION['carrito'] = [];

echo json_encode([
    'ok'        => true,
    'pedido_id' => $pedido_ref,
]);
