<?
class PanelRenderer implements GitPHPRenderer
{
    public function render($elements)
    {
        chdir("panel");
        require "index.php";
        chdir("..");
    }
}

$renderer = new PanelRenderer();
GitPHP::register_action("default", $renderer);
GitPHP::register_action("test", $renderer);
?>
