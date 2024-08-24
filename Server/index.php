<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
ini_set("log_errors", 1);
ini_set('error_log', '/proc/1/fd/1');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Warsaw');

setcookie("autologin", getenv("autologinCookie"), time() + (10 * 365 * 24 * 60 * 60));

require_once "GitPHP/GitPHP.php";
require_once "GitHome/GitHome.php";

GitPHP::$SQLITE_DB_PATH = "/data/GitHome.db";
try
{
    GitHome::index();
}
catch (Throwable $e)
{
    echo "<br><br>";
    GitHome::die($e->getTraceAsString() . "\n" . $e->getMessage());
}

?>