<?php

class GitHomeTemperature implements GitPHPAction
{
    function __construct()
    {
        GitPHP::register_action("temperature", $this);
    }

    public function render($elements)
    {
        if (!isset($elements[1]))
            GitHome::die("Temperature: No subaction specified.");

        if ($elements[1] == "get")
        {
            if (!isset($elements[2]))
                GitHome::die("Temperature: No sensor specified.");

            if (!isset($elements[3]))
                GitHome::die("Temperature: No time span specified.");

            $since = date('Y-m-d H:i:s', strtotime('-1 ' . $elements[3], time()));
    
            echo json_encode($this->getTemperature($elements[2], $since));
            die;
        }
        if ($elements[1] == "add")
        {
            if (!isset($elements[2]))
                GitHome::die("Temperature: No sensor specified.");

            if (!isset($elements[3]))
                GitHome::die("Temperature: No value specified.");

            $this->addTemperature($elements[2], $elements[3]);
            die;
        }

        GitHome::die("Temperature: No valid subaction.");
    }

    public function static($file){}

    private function getTemperature($sensor, $since)
    {
        if($stmt = GitPHP::db()->prepare("SELECT date, value FROM temperature WHERE date > :since AND sensor = :sensor"))
        {
            $stmt->bindParam(':since', $since);
            $stmt->bindParam(':sensor', $sensor);
            if($stmt->execute())
            {
                if($row = $stmt->fetchAll(PDO::FETCH_ASSOC))
                {
                    return $row;
                }
                else
                {
                    GitHome::logError("Temperature: Get temperature for {$sensor} unsuccesful! (1)");
                }
            }
            else
            {
                GitHome::logError("Temperature: Get temperature for {$sensor} unsuccesful! (1)");
            }
        }
        return null;
    }

    private function addTemperature($sensor, $value)
    {
        if ($stmt = GitPHP::db()->prepare("INSERT INTO temperature (date, sensor, value) VALUES (CURRENT_TIMESTAMP, :sensor, :value)"))
        {
            $stmt->bindValue(':sensor', $sensor);
            $stmt->bindValue(':value', $value);
            if ($stmt->execute())
            {
                return true;
            }
            else
            {
                GitHome::logError("Temperature: Add temperature for {$sensor} unsuccesful! (2)");
            }
        }
        else
        {
            GitHome::logError("Temperature: Add temperature for {$sensor} unsuccesful! (1)");
        }
        return false;
    }
}
?>