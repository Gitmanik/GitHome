<?
class GitHomePanel implements GitPHPAction
{
    function __construct()
    {
        GitPHP::register_action("default", $this);
        GitPHP::register_action("panel", $this);
    }

    public function render($elements)
    {
        chdir (__DIR__ . "/../Panel");
        require "index.php";
    }

    public function filterDevices()
    {
        if (!isset($_GET["filter"]))
            return GitHome::getDevices();

        $idsToShow = explode(",", $_GET["filter"]);
        $arr = array();
        foreach ($idsToShow as $id)
        {
            array_push($arr, GitHomeDevice::createFromID($id));
        }
        return $arr;
    }

    public function static($filename) {return "/GitHome/Panel/{$filename}";}
}
?>
