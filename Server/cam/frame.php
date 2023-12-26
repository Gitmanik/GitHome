<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$frame_image = "/tmp/zosi.jpg";
$nmap_xml = "/tmp/rtsp.xml";

unlink($frame_image);

$command = 'ffmpeg -y -i rtsp://192.168.8.159:554/11 -frames:v 1 ' . $frame_image;
shell_exec($command);

// if (!file_exists($frame_image))
// {
//     unlink ($nmap_xml);
//     $stream = popen('sudo /usr/bin/nmap -p554 192.168.8.* --open --oX ' . $nmap_xml, 'r');

//     while (!feof($stream)) {
//         //Make sure you use semicolon at the end of command
//         $buffer = fread($stream, 1024);
//         echo $buffer, PHP_EOL;
        
//     }
//     echo "<br><br><br>";

//     pclose($stream);

//     $xmlstring = file_get_contents($nmap_xml);
//     $xml = simplexml_load_string($xmlstring, "SimpleXMLElement", LIBXML_NOCDATA);
//     $json = json_encode($xml);
//     $array = json_decode($json, true);
//     $array = $array['host'];
//     foreach ($array as $key => $value)
//     {
//         echo $key . "<br>";
//         print_r($value);
//         echo "<br><br><br>";
//         if ($value[1]['addr'] == '74:19:F8:DE:55:7C')
//         {
//             echo "hyooo";
//         }
//     }
//     die;
// }

echo file_get_contents($frame_image);
?>
