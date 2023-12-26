<?php
require_once "pdo_connect.php";
require_once "device.php";
if (!isset($_POST["id"])) die;

$dev = Device::fromDeviceID($pdo, $_POST["id"]);
$dev->action($pdo);

?>