<?php

class BlindBridge extends GitHomeDevice
{
    public array $sendQueue = array();

    public function endpoint($elements)
    {
        $this->legacy($elements);
    }

    public function render()
    {
    }
    public function legacy($elements)
    {
        if (count($this->sendQueue) > 0)
        {
            echo $this->sendQueue[0];
            array_shift($this->sendQueue);
        }
    }

    public function send($protocol, $code)
    {
        if ($protocol == -1) // RAW
        {
            array_push($this->sendQueue, "R" . $code);
        }
        else
        {
            // TODO: SEND PROTOCOL
            array_push($this->sendQueue, $code);
        }
        $this->save();
    }
}

?>