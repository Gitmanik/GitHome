<?php
require_once "pdo_connect.php";
require_once "device.php";

$dev = Device::fromDeviceID($pdo, $_GET["id"]);

if ($dev == false)
    die;

$dev->log($pdo, "Aktualizacja " . $dev->name);

// $file = "../firmware/" . $dev->firmware . ".bin";
// $content=file_get_contents($file);
// header("Content-Length: ". filesize($file));
if($stmt = $pdo->prepare("SELECT file FROM versions WHERE firmware = :firmware order by version desc limit 1")){
    $stmt->bindValue(":firmware", $dev->firmware);
    
    if($stmt->execute())
    {
        if($stmt->rowCount() == 0)
        {
            echo "kraksa";
        }
        else
        {
            $stmt->bindColumn(1, $content, PDO::PARAM_LOB);
            $stmt->fetch();
        }
    }
}


header("Content-Type: application/octet-stream");
header('Content-Disposition: attachment; filename="' . $dev->firmware . '.bin"');
header("Content-Length: ". strlen($content));
echo $content;

?>