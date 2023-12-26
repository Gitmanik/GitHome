<?php
require_once "pdo_connect.php";
require_once "device.php";

if (!isset($_POST["id"])) die("id");
if (!isset($_POST["data"])) die("data");

$dev = Device::fromDeviceID($pdo, $_POST["id"]);

if (!$dev)
{
    require_once("common.php");
    put_log($pdo, $_POST["id"], $_POST["data"]);
}
else
{
    $dev->log($pdo, $_POST["data"]);
}



?>