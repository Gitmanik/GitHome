<?php

class RTSPDevice extends GitHomeDevice
{
    public ?string $stream;

    public function endpoint($elements)
    {
        if (!isset($elements[2]))
        {
            $this->logError("Subaction not specified!");
            die;
        }
        switch ($elements[2])
        {
            case "view";
                $this->renderView();
                break;
            case "frame":
                $this->renderFrame();
                break;
            default:
                GitHome::die("RTSP: No valid endpoint.");
                break;
        }
    }

    public function render()
    {
        echo "<button class='toggle toggle_momentary' onclick='location.href=`/device/{$this->id}/view`;'>{$this->name}</button>";
    }

    private function renderView()
    {
        echo "
        <div class=\"frame\" style=\"width:100%; height:100%\">
            <img>
        </div>
        <script>
            function refreshFrame(){    
            var timestamp = new Date().getTime();  
            var queryString = '/device/{$this->id}/frame';
            var imageElement = document.querySelector(\".frame img\");  
            
            var downloadingImage = new Image();
            downloadingImage.onload = function(){
                imageElement.src = this.src;   
                refreshFrame();
            };

            downloadingImage.src = queryString;    
        }

        refreshFrame();
        </script>";
    }

    private function renderFrame()
    {
        $frame_image = "/tmp/rtsp_{$this->id}.jpg";
        unlink($frame_image);

        $command = "ffmpeg -rtsp_transport tcp -y -i rtsp://{$this->stream} -frames:v 1 {$frame_image}";
        shell_exec($command);
        echo file_get_contents($frame_image);
    }
}

?>