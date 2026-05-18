<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/conexion.php";

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') { //para asegurar que el usuario sea admin
    $loginUrl = BASE_URL . '/login.php';
    header("Location: $loginUrl");
    exit();
}
