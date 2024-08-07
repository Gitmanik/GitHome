<?php
require_once "GitPHP/GitPHP.php";

$all_renderers_files = glob("renderers/*.php");
foreach ($all_renderers_files as $renderer)
{
    require $renderer;
}

$all_devices_types = glob("devices/*.php");
foreach ($all_devices_types as $device_type)
{
    require $device_type;
}

$path = parse_url($_SERVER['REQUEST_URI'])['path'];
$path = ltrim($path, '/');

$elements = explode('/', $path);

if ($elements[0] == "")
{
    GitPHP::get_action("default")->render($elements);
    exit();
}

$action = GitPHP::get_action($elements[0]);
if ($action != null)
{
    $action->render($elements);
    exit();
}
// GitPHP::fatal()
echo "NOT REGISTERED ACTION<br>";
var_dump($elements);
?>