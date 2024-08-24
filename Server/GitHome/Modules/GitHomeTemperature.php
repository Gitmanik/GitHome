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
    
            echo json_encode(GitHomeTemperature::getTemperature($elements[2], $since));
            die;
        }
        if ($elements[1] == "add")
        {
            if (!isset($elements[2]))
                GitHome::die("Temperature: No sensor specified.");

            if (!isset($elements[3]))
                GitHome::die("Temperature: No value specified.");

            GitHomeTemperature::addTemperature($elements[2], $elements[3]);
            die;
        }

        GitHome::die("Temperature: No valid subaction.");
    }

    public function static($file){}

    public static function getTemperature($sensor, $since)
    {
        $stmt = GitHome::db()->prepare("SELECT date, value FROM temperature WHERE date > :since AND sensor = :sensor");
        $stmt->bindParam(':since', $since);
        $stmt->bindParam(':sensor', $sensor);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row;
    }

    public static function addTemperature($sensor, $value)
    {
        $stmt = GitHome::db()->prepare("INSERT INTO temperature (date, sensor, value) VALUES (:ts, :sensor, :value)");
        $ts = GitPHP::CURRENT_TIMESTAMP(); $stmt->bindValue(":ts", $ts);
        $stmt->bindValue(':sensor', $sensor);
        $stmt->bindValue(':value', $value);
        $stmt->execute();
    }
}
?>