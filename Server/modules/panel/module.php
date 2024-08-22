<?
class PanelRenderer implements GitPHPAction
{
    function __construct()
    {
        GitPHP::register_action("default", $this);
        GitPHP::register_action("panel", $this);
    }

    public function render($elements)
    {
        chdir(__DIR__ . "/panel");
        require "index.php";
        chdir("..");
    }

    public function static($filename) {return "/modules/panel/panel/{$filename}";}
}
?>
