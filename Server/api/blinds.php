<?php
require_once "pdo_connect.php";
require_once "common.php";
require_once "device.php";

if (!isset($_POST["blind_id"])) die;
if (!isset($_POST["c"])) die;

$dev = Device::fromDeviceID($pdo, "ESP-D01E18");

if (isset($_POST['blind_id']))
{
    foreach (get_came_blinds() as $b)
    {
        if ($b["id"] == $_POST["blind_id"])
        {
            $dev->click($pdo, $b, $_POST['c']);
            die;
        }
    }
    foreach (get_custom_blinds() as $b)
    {
        if ('C' . $b["id"] == $_POST["blind_id"])
        {
            $dev->click_custom($pdo, $b, $_POST['c']);
            die;
        }
    }
}

?>