<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {

    $mongoClient = new MongoDB\Client(
        "mongodb+srv://USUARIO:PASSWORD@cluster0.nptlrpe.mongodb.net/dbstore?retryWrites=true&w=majority&appName=Cluster0"
    );

    $db = $mongoClient->selectDatabase("dbstore");

    echo "Conexión exitosa 🚀";

} catch (Exception $e) {

    die("Error: " . $e->getMessage());

}