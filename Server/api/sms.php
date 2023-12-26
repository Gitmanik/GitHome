<?php
require_once "device.php";
require_once "common.php";

function parse_command($command)
{
    require "pdo_connect.php";
    $sms = explode(' ', $command);

    if (is_numeric($sms[0]))
    {
        $dev = Device::fromInternalID($pdo, $sms[0]);
        return $dev->action($pdo, $sms);
    }
    
    $return = "";
    switch (strtolower($sms[0]))
    {
        case 'rmlog':
            if (count ($sms) > 1 && is_numeric($sms[1]))
            {
                if($stmt = $pdo->prepare("DELETE FROM logs ORDER by id desc limit " . $sms[1])){
                    $stmt->execute();
                    $return .= "bylo i nie ma";
                }
            }
            break;
        
        case 'logs':
            if (count($sms) > 1 && is_numeric($sms[1]))
            {
                $logs = get_logs(intval($sms[1]));
                foreach ($logs as $l)
                {
                    $return .= sprintf("%s - %s\n", $l['date'], $l['data']);
                }
            }
        break;
        
        case 'status':

            $return .= "Rolety:\n";
            $blinds = get_came_blinds();
            foreach ($blinds as $b)
            {
                $return .= sprintf("%s %s - %s\n", $b['id'], $b['name'], $b['last_command']);
            }
            $return .= '\n';

            $boxes = get_boxes();
            foreach ($boxes as $box)
            {
                if (!$box->visible) continue;
                ob_start();
                $box->evalCode($pdo);
                $return .= sprintf("%s:\n%s\n", $box->name, ob_get_clean());
            }
            $devices = get_devices();
            foreach ($devices as $dev)
            {
                if (!$dev->visible || $dev->hidden) continue;
                $return .= sprintf("%s %s - %s\n", $dev->id, $dev->name, $dev->getStatus($pdo));
            }
            break;
        case 'roleta':
        case 'r':
            if (count($sms) != 3)
            {
                $return .= "Obsluga: roleta {id} {D/S/G}";
                break;
            }
            $blind = get_came_blinds()[intval($sms[1]) - 1];

            Device::fromDeviceID($pdo, "ESP-D01E18")->click($pdo, $blind, $sms[2]);
            $return .= sprintf("Roleta : %s %s", $blind['name'], strtoupper($sms[2]));

            break;
    }
    $return = str_replace("</br>", "\n", $return);
    return $return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    echo parse_command($_POST['content']);
}
?>