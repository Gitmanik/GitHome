<?php
try{
    $username = getenv('DB_USERNAME');
    $password = getenv('DB_PASSWORD');

    $pdo = new PDO("mysql:charset=utf8mb4;host=db;dbname=smarthome", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
