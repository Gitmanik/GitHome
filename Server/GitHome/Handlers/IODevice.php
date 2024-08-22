<?php

class IODevice extends GitHomeDevice
{
    public ?string $in = null;
    public ?string $out = null;

    public function endpoint($elements)
    {

    }
    public function legacy($elements)
    {
        if (!is_null($this->out))
        {
            echo $this->out;
            $this->out = null;
        }
        if (isset($_GET["data"]))
        {
            $this->in = $_GET["data"];
        }
    }
}

?>