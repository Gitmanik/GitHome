<?php

class ToggleDevice extends GitHomeDevice
{
    public bool $state = false;
    public bool $momentary = false;

    public function endpoint($elements)
    {
        if (!isset($elements[2]))
        {
            $this->logError("Subaction not specified!");
            die;
        }
        switch (strtoupper($elements[2]))
        {
            case 'GET':
                $this->handleGet();
                break;
            case 'ON':
                $this->toggleON();
                break;
            case 'OFF':
                $this->toggleOFF();
                break;
            case 'TOGGLE':
                $this->toggle();
                break;
            default:
                $this->logError("Wrong subaction! {$elements[2]}");
                break;
        }
    }

    public function legacy($elements)
    {
        $this->handleGet();
    }

    public function render()
    {
        $class = $this->momentary ? "momentary" : ($this->state ? "true" : "false");
        echo sprintf('<button class="toggle toggle_%s" onclick=GitHome_fetch("/device/%s/TOGGLE");>%s</button>', $class, $this->id, $this->name);
    }

    private function handleGet()
    {
        echo var_export($this->state);
        if ($this->momentary)
            $this->state = false;
    }

    public function toggleON()
    {
        if (!$this->state)
        {
            $this->state = true;
            $this->logNormal("ON");
        }
    }

    public function toggleOFF()
    {
        if ($this->state)
        {
            $this->state = false;
            $this->logNormal("OFF");
        }
    }
    public function toggle()
    {
        $this->state = !$this->state;
        $this->logNormal("Toggled " . ($this->state ? "ON" : "OFF"));
    }
}

?>