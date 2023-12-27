<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>SmartDom</title>
	<link rel="stylesheet" href="/main.css?filever=<?php echo filemtime('../main.css')?>">
	<script src="/common.js?filever=<?php echo filemtime('../common.js')?>"></script>
	<link rel="icon" type="image/png" href="/favicon.png">
	
	<link rel="stylesheet" href="index.css?filever=<?php echo filemtime('index.css')?>">
	<script src="Chart.min.js"></script> <!-- https://www.chartjs.org/ -->
	<script src="index.js?filever=<?php echo filemtime('index.js')?>"></script>
</head>
<body>
	<p class="header"><a href="/config/">SmartDom</a></p>
	<ul class="devices"> 
	
	<?php
		error_reporting(E_ERROR);
		require_once "../api/common.php";

		install_cookie();

		require_once "../api/pdo_connect.php";
		echo sprintf("<script> var temperatureData = %s;</script>", json_encode(get_temperatures(date('Y-m-d H:i:s', strtotime('-1 week', time()))), true));
		$boxes = get_boxes();

		foreach ($boxes as $box)
		{
			if (!$box->visible) continue;
			echo sprintf('<li class="device box border" box_id=%s>', $box->id);
			// echo sprintf('<div class="name"> <h2 class=cell>%s</h2></div>', $box->name);
			echo '<div class="data">';
			$box->evalCode($pdo);
			echo "</div></li>";
		}
		
		// echo "</ul><ul class='devices>";
		$devices = get_devices();
		foreach ($devices as $dev)
		{
			if (!$dev->visible || $dev->hidden) continue;
			echo sprintf('<li class="device border" device_id=%s>', $dev->device_id);
			// echo sprintf('<div class="name"> <h2 class=cell>%s</h2></div>', $dev->name);
			switch ($dev->type)
			{
					case "TOGGLE":
						echo sprintf('<button class="toggle buttonstate_%s" device_id=%s onclick=toggle(this);>%s</button>', $dev->data == "true" ? 'true' : 'false', $dev->device_id, $dev->name);
						break;
					case 'MOMENTARY':
						echo sprintf('<button class="momentary_toggle" device_id=%s onclick=toggle(this);>%s</button>', $dev->device_id, $dev->name);
						break;
					case 'DATA':
						echo sprintf('<div class="data" device_id=%s>%s</div>', $dev->device_id, $dev->parseData($pdo));
						break;
			}
			echo "</li>";
		}

	?>
	</ul>
	<div class="chart">
        <canvas id="chart"></canvas>
	</div>
</body>

</html>