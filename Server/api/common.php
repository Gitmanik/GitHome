<?php
require_once "device.php";
require_once "box.php";
date_default_timezone_set('Europe/Warsaw');

function install_cookie()
{
    setcookie("autologin", "saDdEbt5ocvEh7aO3E5wSSbW4u3", time() + (10 * 365 * 24 * 60 * 60));
}

function put_log($pdo, $id, $data, $level = 0)
{
    if($stmt = $pdo->prepare("INSERT INTO logs (device_id, data, level) VALUES (:id, :data, :level)")){
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':level', $level);
        $stmt->execute();
    }
}

function get_logs($count, $level = 0)
{
    require "pdo_connect.php";
    if($stmt = $pdo->prepare("SELECT * FROM logs WHERE level <= :level order by id desc limit " . $count)){
        $stmt->bindParam(":level", $level);
        if($stmt->execute())
        {
            return $stmt->fetchAll();
        }
    }
}

function get_came_blinds()
{
    require "pdo_connect.php";
    if($stmt = $pdo->prepare("SELECT * FROM came_blinds order by id")){
        if($stmt->execute())
        {
            return $stmt->fetchAll();
        }
    }
}

function get_custom_blinds()
{
    require "pdo_connect.php";
    if($stmt = $pdo->prepare("SELECT * FROM custom_blinds order by id")){
        if($stmt->execute())
        {
            return $stmt->fetchAll();
        }
    }
}

function get_all_logs()
{
    require "pdo_connect.php";
    if($stmt = $pdo->prepare("SELECT * FROM logs")){
        if($stmt->execute())
        {
            return $stmt->fetchAll();
        }
    }
}

function get_devices()
{
    require "pdo_connect.php";
    if($stmt = $pdo->prepare("SELECT * FROM devices")){
        if($stmt->execute())
        {
            if($row = $stmt->fetchAll())
            {
                $out = array();
                foreach($row as $item)
                {    
                    $out[$item["device_id"]] = Device::fromRow($item);
                }
                return $out;
            }
            else
            {
                echo "[]";
            }
            unset($stmt);
        }
    }
}

function get_boxes()
{
    require "pdo_connect.php";
    if($stmt = $pdo->prepare("SELECT * FROM boxes")){
        if($stmt->execute())
        {
            if($row = $stmt->fetchAll())
            {
                $out = array();
                foreach($row as $item)
                {    
                    array_push($out, Box::fromRow($item));
                }
                return $out;
            }
            unset($stmt);
        }
    }
}

function get_versions()
{
    require "pdo_connect.php";
    if($stmt = $pdo->prepare("SELECT id, firmware, MAX(version) as version, file FROM versions GROUP BY firmware")){
        if($stmt->execute())
        {
            if($row = $stmt->fetchAll())
            {
                return $row;
            }
            unset($stmt);
        }
    }
}

function get_temperatures($since)
{
    require "pdo_connect.php";
    if($stmt = $pdo->prepare("SELECT * FROM temperature WHERE date > :since")){
        $stmt->bindParam(':since', $since);
        if($stmt->execute())
        {
            if($row = $stmt->fetchAll())
            {
                return $row;
            }
            unset($stmt);
        }
    }
}
?>