<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>SmartDom - Rolety</title>
	<link rel="stylesheet" href="came.css?filever=<?php echo filemtime('came.css')?>">
	<link rel="stylesheet" href="/main.css?filever=<?php echo filemtime('../main.css')?>">
	<link rel="icon" type="image/png" href="/favicon.png">
	<script src="came.js?filever=<?php echo filemtime('came.js')?>"></script>
	<script src="../common.js?filever=<?php echo filemtime('../common.js')?>"></script>
</head>
<body>
	<p class="header"><a href="/">SmartDom</a></p>
	<ul class="devices"> 
	
	<?php
		require_once "../api/common.php";
		require_once "../api/pdo_connect.php";

		$custom = get_custom_blinds();
		foreach ($custom as &$c)
		{
			$c['id'] = "C" . $c['id'];
		}

		$blinds = array_merge(get_came_blinds(), $custom);
		foreach ($blinds as $b)
		{
			echo '<li class="box border">';
			echo sprintf('<div class="name"> <h2>%s</h2></div>', $b['name']);

			echo sprintf('<div class="buttons"> <button class="%s" onclick="toggle(`%s`, `G`);">🔼</button>', $b['last_command'] == 'G' ? 'last_command' : 'command', $b['id'], $b['name']);
			echo sprintf('<button class="%s" onclick="toggle(`%s`, `S`);">⏺️</button>', $b['last_command'] == 'S' ? 'last_command' : 'command', $b['id'], $b['name']);
			echo sprintf('<button class="%s" onclick="toggle(`%s`, `D`);">🔽</button>', $b['last_command'] == 'D' ? 'last_command' : 'command', $b['id'], $b['name']);
			echo "</div></li>";
		}

	?>
	</ul>
</body>

</html>