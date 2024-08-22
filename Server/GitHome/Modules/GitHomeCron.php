<?php

class GitHomeCron implements GitPHPAction 
{
    function __construct()
    {
        GitPHP::register_action("cron", $this);
    }

    public function render($elements)
    {

    }
    public function static($file) {}
}

?>