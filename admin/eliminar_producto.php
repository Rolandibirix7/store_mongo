<?php
require_once("../config/conexion.php");
include("../config/auth.php");

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
$cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$redirect_url = BASE_URL . "/admin/dashboard.php" . (!empty($cat) ? "?cat=" . urlencode($cat) : "");

try {
    $oid = new MongoDB\BSON\ObjectId($id);
} catch(Exception $e){
    header("Location: " . $redirect_url);
    exit();
}

$col_productos->deleteOne(['_id' => $oid]);

header("Location: " . $redirect_url);
exit();
