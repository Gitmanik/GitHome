<?php
class Device {
    public $id, $device_id;
    public $name;
    public $type,$firmware;
    public $data;
    public $visible;
    public $code;
    public $command;
    public $ip;
    public $lastreport;
    public $version;

    public $supportsData = false;
    public $supportsSending = false;
    public $hidden = false;

    function __construct($item)
    {
        $this->id = $item["id"];
        $this->device_id = $item["device_id"];
        $this->type = $item["type"];
        $this->name = $item["name"];
        $this->firmware = $item["firmware"];
        $this->version = $item["version"];
        $this->visible = $item["visible"] == "1";
        $this->ip = $item["ip"];
        $this->lastreport = $item["lastreport"];
        
        $this->data = base64_decode($item["data"]);
        $this->code = base64_decode($item['code']);
        $this->command = base64_decode($item['command']);
    }

    public static function insert($pdo, $id, $version)
    {
        if($stmt = $pdo->prepare("INSERT INTO devices (device_id, name, version, data, type, firmware, ip, lastreport) VALUES (:device_id, :name, :version, :data, 'TOGGLE', 'TOGGLE', '" . $_SERVER['REMOTE_ADDR'] . "', CURRENT_TIMESTAMP)")) {
            $stmt->bindValue(':device_id', $id);
            $stmt->bindValue(':name', $id);
            $stmt->bindValue(':version', $version);
            $stmt->bindValue(':data', base64_encode("false"));
            $stmt->execute();
        }

        return Device::fromDeviceID($id, $id);
    }

    public static function fromRow($row)
    {
        switch (strtolower($row["type"]))
        {
            case "toggle":
                return new ToggleDevice($row);
            case "momentary":
                return new MomentaryDevice($row);
            case "data":
                return new DataDevice($row);
            case "action":
                return new ActionDevice($row);
            case "rfbridge":
                return new RfBridgeDevice($row);
            default:
                return new Device($row);
        }
    }
    
    public static function fromDeviceID($pdo, $id)
    {
        if($stmt = $pdo->prepare("SELECT * FROM devices WHERE device_id = :id")){
            $stmt->bindValue(':id', $id);
            if($stmt->execute())
            {
                if($row = $stmt->fetch())
                {
                    return Device::fromRow($row);
                }
            }
        }
        return false;
    }
    
    public static function fromInternalID($pdo, int $id)
    {
        if($stmt = $pdo->prepare("SELECT * FROM devices WHERE id = :id")){
            $stmt->bindValue(':id', $id);
            if($stmt->execute())
            {
                if($row = $stmt->fetch())
                {
                    return Device::fromRow($row);
                }
            }
        }
        return false;
    }
    
    public function log($pdo, $data, $level = 0)
    {
        require_once("common.php");
        put_log($pdo, $this->device_id, $data, $level);
    }

    public function updateStatistics($pdo)
    {
        if($stmt = $pdo->prepare("UPDATE devices SET lastreport = CURRENT_TIMESTAMP, ip = :ip WHERE device_id = :device_id")){
            $stmt->bindValue(':device_id', $this->device_id);
            $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] );
            
            if (!$stmt->execute())
            {
                echo "err";
            }
        }
    }

    public function updateSQL($pdo)
    {
        if($stmt = $pdo->prepare("UPDATE devices SET data = :data, command = :command, version = :version WHERE device_id = :device_id")){
            $stmt->bindValue(':device_id', $this->device_id);
            $stmt->bindValue(':version', $this->version);
            $stmt->bindValue(':data', base64_encode($this->data));
            $stmt->bindValue(':command', base64_encode($this->command));
            
            if (!$stmt->execute())
            {
                echo "err";
            }
        }
    }
    
    public function shouldUpdate($pdo)
    {
        if($stmt = $pdo->prepare("SELECT version FROM versions WHERE firmware = :firmware ORDER BY version desc limit 1")){
            $stmt->bindValue(":firmware", $this->firmware);
            
            if($stmt->execute())
            {
                if($stmt->rowCount() == 0)
                {
                    if($stmt = $pdo->prepare("INSERT INTO versions (firmware, version, file) VALUES (:firmware, :version, null)")) {
                        $stmt->bindValue(':firmware', $this->firmware);
                        $stmt->bindValue(':version', $this->version);
                        $stmt->execute();
                    }
                    return false;
                }
                else
                {
                    if($row = $stmt->fetch())
                    {
                        return intval($row["version"]) > $this->version;
                    }
                }
            }
        }
        return false;
    }

    public function ping($pdo)
    {
    }

    public function runCustomCode($pdo)
    {
        if (!empty($this->code))
        {
            $hour = intval(date("G"));
            $minute = intval(date("i"));
            $second = intval(date("s"));
            eval($this->code);
        }
    }

    public function action($pdo)
    {

    }

    public function getStatus($pdo)
    {
        return "";
    }
    
}

class ToggleDevice extends Device {

    public function ToggleON($pdo)
    {
        return $this->SetState($pdo, true);
    }

    public function ToggleOFF($pdo)
    {
        return $this->SetState($pdo, false);
    }

    public function Toggle($pdo)
    {
        return $this->SetState($pdo, !$this->parseState());
    }
    
    public function parseState()
    {
        return $this->data == "true";
    }

    public function getFancyState()
    {
        return $this->parseState() ? "Włączono" : "Wyłączono";
    }

    public function SetState($pdo, $state)
    {
        if (($this->data == "true") != $state)
        {
            $this->data = $state ? "true" : "false";
            $this->updateSQL($pdo);
        }
        return $this->getFancyState() . " " . $this->name;
    }

    public function ping($pdo)
    {
        echo var_export($this->parseState());
    }

    public function action($pdo)
    {   
        $ret =  $this->Toggle($pdo);
        $this->log($pdo, sprintf("Przelaczono %s: %s.", $this->name, $this->getFancyState()));
        return $ret;
    }

    public function getStatus($pdo)
    {
        return $this->parseState() ? 'Włączony' : 'Wyłączony';
    }
}

class MomentaryDevice extends ToggleDevice
{
    public function getStatus($pdo)
    {
        return "X";
    }
    public function ping($pdo)
    {
        $stan = $this->parseState();
        echo var_export($stan);
        if ($stan)
        {
            $this->ToggleOFF($pdo);
        }
    }
}

class DataDevice extends Device {

    public $supportsData = true;
    public $supportsSending = true;

    public function ping($pdo)
    {
        if (!empty($this->command))
        {
            echo $this->command;
            $this->command = "";
        }
        $this->updateSQL($pdo);
    }
    public function getStatus($pdo)
    {
        return $this->data;
    }
}

class ActionDevice extends Device
{
    public $hidden = true;
}

class RfBridgeDevice extends Device
{
    public $hidden = true;
    public $supportsSending = true;
    
    public function ping($pdo)
    {
        if (!empty($this->command))
        {
            $commands = explode("\n", $this->command);
            echo $commands[0];
            array_shift($commands);
            $this->command = implode("\n",$commands);
        }
        parent::ping($pdo);
        $this->updateSQL($pdo);
    }

    public function click($pdo, $b, $c)
    {
        $c = strtoupper($c);
        $code = $b['code'];

        switch($c)
        {
            case 'G':
                $code++;
                break;
            case 'D':
                $code--;
                break;
            default:
                break;
        }

        $this->push($code);
        $this->updateSQL($pdo);

        if($stmt = $pdo->prepare("UPDATE came_blinds SET last_command = :last_command WHERE id = :id")){
            $stmt->bindValue(':id', $b['id']);
            $stmt->bindValue(':last_command', $c);
            $stmt->execute();
        }

        $this->log($pdo, "Rolety: " . $b['name'] . " " . $c);
    }

    public function click_custom($pdo, $b, $c)
    {
        $c = strtoupper($c);

        switch($c)
        {
            case 'G':
                $code = $b['up'];
                break;
            case 'D':
                $code = $b['down'];
                break;
            default:
                $code = $b['stop'];
                break;
        }

        $code = 'R' . $code;

        $this->push($code);
        $this->updateSQL($pdo);

        if($stmt = $pdo->prepare("UPDATE custom_blinds SET last_command = :last_command WHERE id = :id")){
            $stmt->bindValue(':id', $b['id']);
            $stmt->bindValue(':last_command', $c);
            $stmt->execute();
        }

        $this->log($pdo, "Rolety: " . $b['name'] . " " . $c);
    }

    public function push($data)
    {
        $commands = explode("\n", $this->command);
        array_push($commands, $data);
        $this->command = implode("\n",$commands);
    }
}

?>