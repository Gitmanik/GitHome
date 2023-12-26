<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>SmartDom - Terminal</title>
    <meta http-equiv="refresh" content="5">
    <link rel="stylesheet" href="terminal.css?filever=<?php echo filemtime('terminal.css') ?>">
    <script src="../common.js?filever=<?php echo filemtime('../common.js') ?>"></script>
    <script src="jquery-1.12.4.min.js"></script>
</head>

<body>
    <div class="devices">

        <?php

		require_once "../api/common.php";

		require_once "../api/pdo_connect.php";
		$boxes = get_boxes();

		$ignored_devices = array(18, 33);
		$ignored_boxes = array(5,6);
		$items_in_row = 3;

		$ctr = 0;
		$devices = get_devices();

		echo "<table><tr>";

		foreach ($boxes as $box) {
			if (in_array($box->id, $ignored_boxes)) continue;
			if (!$box->visible) continue;

			echo sprintf('<td class="box" box_id=%s>', $box->id);
			echo sprintf('<div class="name"> <h2 class=cell>%s</h2></div>', $box->name);
			echo '<div class="data">';
			$box->evalCode($pdo);
			echo "</div></td>";
			$ctr++;
			if ($ctr % $items_in_row == 0)
				echo "</tr><tr>";
		}

		foreach ($devices as $dev) {
			if (in_array($dev->id, $ignored_devices)) continue;
			if (!$dev->visible || $dev->hidden) continue;

			echo sprintf('<td class="box" device_id=%s>', $dev->device_id);
			echo sprintf('<div class="name"> <h2 class=cell>%s</h2></div>', $dev->name);
			switch ($dev->type) {
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
			$ctr++;
			echo "</td>";
			if ($ctr % $items_in_row == 0)
				echo "</tr><tr>";
		}
		echo "</tr></table>";

		?>
        </ul>
</body>
<script src="terminal.js?filever=<?php echo filemtime('terminal.js') ?>"></script>

</html>
