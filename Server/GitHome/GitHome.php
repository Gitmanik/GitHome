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
        new GitHomeConfig();
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

    public static function logNormal(string $text, GitHomeDevice $dev = null) { GitPHP::logNormal($text, is_null($dev) ? "GitHome" : $dev->id);}
    public static function logError($text, GitHomeDevice $dev = null) { GitPHP::logError($text, is_null($dev) ? "GitHome" : $dev->id);}
    public static function logWarn($text, GitHomeDevice $dev = null) { GitPHP::logWarn($text, is_null($dev) ? "GitHome" : $dev->id);}
    public static function getLogs($maxCount, $minLevel = PHP_INT_MAX) {return GitPHP::getLogs($maxCount, $minLevel);}

    public static function die(string $reason, bool $silent = false)
    {
        $text = "GitHome died. {$reason}";
        if (!$silent)
            echo $text;
        GitPHP::logError($text);
        error_log($text);
        die;
    }

    public static function db() { return GitPHP::db(); }

    public function static($file) {}
}
?>