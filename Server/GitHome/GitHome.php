<?php

class GitHome implements GitPHPAction
{
    public static GitHomeFirmwareServer $firmware;
    public static array $handlers;

    public static function index()
    {
        foreach (glob(__DIR__ . "/Modules/*.php") as $module)
        {
            require_once $module;
        }

        foreach (glob(__DIR__ . "/Handlers/*.php") as $device)
        {
            require_once $device;
        }

        GitPHP::index();
    }

    function __construct()
    {
        GitHome::$firmware = new GitHomeFirmwareServer();
        GitHome::$handlers = array();

        foreach (get_declared_classes() as $class)
            if (is_subclass_of($class, 'GitHomeDevice'))
                array_push(GitHome::$handlers, $class);

        GitPHP::register_action("debug", $this);
        GitPHP::register_action("device", $this);
    }

    public function render($elements)
    {
        switch ($elements[0])
        {
            case "debug":
                $this->handleDebug($elements);
                break;
            case "device":
                $this->handleDevice($elements);
                break;
        }
    }

    private function handleDevice($elements)
    {
        if (!isset($elements[1]))
            GitHome::die("handleDevice: No device specified!");

        $dev = GitHomeDevice::createFromID($elements[1]);

        if ($dev == null)
            GitHome::die("handleDevice: Device with id {$elements[1]} does not exist");

        $dev->endpoint($elements);
    }

    private function handleDebug($elements)
    {
        if (!isset($elements[1]))
            GitHome::die("handleDebug: No subaction specified!");

        if ($elements[1] == "handlers")
        {
            var_dump(GitHome::$handlers);
            die;
        }
        
        if ($elements[1] == "devices")
        {
            $f = "%";
            if (isset($elements[2]))
                $f = $elements[2];
            
            var_dump(GitHome::getDevices($f));
            die;
        }
        
        if ($elements[1] == "render")
        {
            $dev = GitHomeDevice::createFromID($elements[2]);
            $dev->render();
            die;
        }

        GitHome::die("handleDebug: Invalid subaction specified!");
    }
    public static function getDevices($handler_filter = "%")
    {
        $out = array();
        $stmt = GitPHP::db()->prepare("SELECT * FROM devices WHERE id LIKE :filter");
        $stmt->bindParam("filter", $handler_filter);

        $stmt->execute();

        $rows = $stmt->fetchAll();
        foreach($rows as $row)
        {    
            array_push($out, GitHomeDevice::createFromRow($row));
        }
        return $out;
    }

    public static function logNormal(string $text, GitHomeDevice $dev = null) { GitHome::logCommon($text, is_null($dev) ? "GitHome" : $dev->id, 0);}
    public static function logWarn($text, GitHomeDevice $dev = null) { GitHome::logCommon($text, is_null($dev) ? "GitHome" : $dev->id, 1);}
    public static function logError($text, GitHomeDevice $dev = null) { GitHome::logCommon($text, is_null($dev) ? "GitHome" : $dev->id, 2);}

    public static function logCommon($data, $id, $level)
    {
        if($stmt = GitPHP::db()->prepare("INSERT INTO logs (device_id, data, level, date) VALUES (:id, :data, :level, :time)")){
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':data', $data);
            $stmt->bindParam(':level', $level);
            $ts = GitPHP::CURRENT_TIMESTAMP(); $stmt->bindParam(':time', $ts);
            $stmt->execute();
        }
    }

    public static function getLogs($maxCount, $minLevel = PHP_INT_MAX)
    {
        if($stmt = GitPHP::db()->prepare("SELECT * FROM logs WHERE level <= :level ORDER BY id DESC LIMIT :count")){
            $stmt->bindParam(":level", $minLevel);
            $stmt->bindParam(":count", $maxCount);
            if($stmt->execute())
            {
                return $stmt->fetchAll();
            }
        }
    }

    public static function die(string $reason, bool $silent = false)
    {
        $text = "GitHome died. {$reason}";
        if (!$silent)
            echo $text;
        GitHome::logError($text);
        error_log($text);
        die;
    }

    public static function db() { return GitPHP::db(); }

    public function static($file) {}
}
?>