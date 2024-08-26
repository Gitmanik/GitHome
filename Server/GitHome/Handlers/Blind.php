<?php 

class Blind extends GitHomeDevice
{
    public string $bridge;
    public int $protocol;
    public string $codeUp;
    public string $codeStop;
    public string $codeDown;
    public int $blindState = 0;

    public function endpoint($elements)
    {
        if (!isset($elements[2]))
        {
            $this->logError("Subaction not specified!");
            die;
        }
        switch (strtoupper($elements[2]))
        {
            case 'UP':
                $this->click(1);
                break;
            case 'STOP':
                $this->click(0);
                break;
            case 'DOWN':
                $this->click(-1);
                break;
            default:
                $this->logError("Wrong direction! {$elements[2]}");
                break;
        }
        $this->save();
    }

    public function click($direction)
    {
        switch ($direction)
        {
            case 1:
                $this->logNormal("Clicked UP");
                $code = $this->codeUp;
                break;
            case 0:
                $this->logNormal("Clicked STOP");
                $code = $this->codeStop;
                break;
            case -1:
                $this->logNormal("Clicked DOWN");
                $code = $this->codeDown;
                break;
            default:
                $this->logError("Wrong direction! {$direction}");
                return false;
        }
       
        $this->blindState = $direction;
        GitHomeDevice::createFromID($this->bridge)->send($this->protocol, $code);
    }

    public function render()
    {
        echo '<div class=blind_container>';
        echo '<div class="blind_name">';
        echo sprintf('<h2>%s</h2>', $this->name);
        echo '</div>';

        echo "<div class='blind_buttons'>";
        echo sprintf('<button class="%s" onclick="GitHome_fetch(`/device/%s/UP`);">&#x21c8</button>', $this->blindState == 1 ? 'blind_currentState' : 'blind_state', $this->id);
        echo sprintf('<button class="%s" onclick="GitHome_fetch(`/device/%s/STOP`);">○</button>', $this->blindState == 0 ? 'blind_currentState' : 'blind_state', $this->id);
        echo sprintf('<button class="%s" onclick="GitHome_fetch(`/device/%s/DOWN`);">&#x21ca</button>', $this->blindState == -1 ? 'blind_currentState' : 'blind_state', $this->id);
        echo "</div>";
        echo "</div>";
    }
}

?>