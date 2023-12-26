<?php
require_once "pdo_connect.php";
require_once "device.php";

$device = Device::fromDeviceID($pdo, $_GET["id"]);
if(!$device)
    $device = Device::insert($pdo, $_GET["id"], $_GET["version"]);

if ($device->version != intval($_GET["version"]))
{
    $device->version = intval($_GET["version"]);
    $device->updateSQL($pdo);
}

if ($device->shouldUpdate($pdo))
    echo "UPDATE";
else
{
    try
    {
        $device->data = $_GET['data'] ?? $device->data; 
        $device->runCustomCode($pdo);
        $device->ping($pdo);
    }
    catch (Throwable $e)
    {
        require_once "common.php";
        put_log($pdo, $device->device_id, $e->getTraceAsString() . "\n" . $e->getMessage(), 2);
    }
}

$device->updateStatistics($pdo);


?>