<?php

class GitHomeLegacy implements GitPHPAction
{
    function __construct()
    {
        GitPHP::register_action("api", $this);
    }

    public function render($elements)
    {
        if (!isset($elements[1]))
            GitHome::die("GitHomeLegacy: No subaction!");

        switch ($elements[1])
        {
            case "report.php":
                $this->handleReport($elements);
                break;
            case "update.php":
                $this->handleUpdate($elements);
                 break;
            default:
                GitHome::die("GitHomeLegacy: Wrong subaction!");
                break;
        }
    }

    private function handleUpdate($elements)
    {
        $dev = GitHomeDevice::createFromID($_GET["id"]);

        if (is_null($dev))
            GitHome::die("GitHomeLegacy handleUpdate: Device not found!");

        $dev->logNormal("Updating firmware");

        $content = GitHome::$firmware->getNewestFirmware($dev->firmware);
        if ($content == null)
        {
            GitHome::die("GitHomeLegacy handleUpdate: Firmware for {$dev->name} not found!");
        }

        $ver = GitHome::$firmware->listVersions($dev->firmware)[0];
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"{$dev->firmware}_{$ver}.bin\"");
        header("Content-Length: ". strlen($content));
        echo $content;
    }

    private function handleReport($elements)
    {
        $dev = GitHomeDevice::createFromID($_GET["id"]);

        if (is_null($dev))
            $dev = GitHomeDevice::createNew($_GET["id"]);

        if (isset($_GET["version"]))
            $dev->version = $_GET["version"];
        else $dev->version = null;

        if ($dev->shouldUpdate())
            echo "UPDATE";
        else
            $dev->legacy($elements);

        $dev->save();
    }
}

?>
