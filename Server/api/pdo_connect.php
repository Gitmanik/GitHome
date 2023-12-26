<?php
try{
    $pdo = new PDO("mysql:charset=utf8mb4;host=localhost;dbname=smartdom", "username", "password");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
