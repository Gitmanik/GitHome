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

    public function static($filename) {return "/GitHome/Panel/{$filename}";}
}
?>
