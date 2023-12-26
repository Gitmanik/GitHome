<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>SmartDom</title>
	<link rel="stylesheet" href="index_old.css?filever=<?php echo filemtime('index_old.css')?>">
	<link rel="icon" type="image/png" href="/favicon.png">
	<script src="Chart.min.js"></script> <!-- https://www.chartjs.org/ -->
	<script src="index.js?filever=<?php echo filemtime('index.js')?>"></script>
	<script src="/common.js?filever=<?php echo filemtime('../common.js')?>"></script>
</head>
<body>
	<p class="header"><a href="/config/">SmartDom</a></p>
	<ul class="devices"> 
	
	<?php
		require_once "../api/common.php";

		install_cookie();

		require_once "../api/pdo_connect.php";
		echo sprintf("<script> var temperatureData = %s;</script>", json_encode(get_temperatures(date('Y-m-d H:i:s', strtotime('-1 week', time()))), true));
		$boxes = get_boxes();

		foreach ($boxes as $box)
		{
			if (!$box->visible) continue;
			echo sprintf('<li class="device box" box_id=%s>', $box->id);
			echo sprintf('<div class="name"> <h2 class=cell>%s</h2></div>', $box->name);
			echo '<div class="data">';
			$box->evalCode($pdo);
			echo "</div></li>";
		}
		
		// echo "</ul><ul class='devices>";
		$devices = get_devices();
		foreach ($devices as $dev)
		{
			if (!$dev->visible || $dev->hidden) continue;
			echo sprintf('<li class="box" device_id=%s>', $dev->device_id);
			echo sprintf('<div class="name"> <h2 class=cell>%s</h2></div>', $dev->name);
			switch ($dev->type)
			{
					case "TOGGLE":
						echo sprintf('<button class="toggle buttonstate_%s" device_id=%s onclick=toggle(this);></button>', $dev->data == "true" ? 'true' : 'false', $dev->device_id);
						break;
					case 'MOMENTARY':
						echo sprintf('<button class="momentary_toggle" device_id=%s onclick=toggle(this);></button>', $dev->device_id);
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