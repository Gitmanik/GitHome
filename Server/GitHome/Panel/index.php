<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>GitHome</title>
	<link rel="stylesheet" href="/main.css">
	<link rel="icon" type="image/png" href="/favicon.png">
	
	<link rel="stylesheet" href="<?php GitPHP::static("index.css") ?>">
	<script src="<?php GitPHP::static("Chart.min.js") ?>"></script>
	<script src="<?php GitPHP::static("index.js") ?>"></script>
	<link rel="stylesheet" href="<?php GitPHP::static("blind.css") ?>">
	<link rel="stylesheet" href="<?php GitPHP::static("toggle.css") ?>">
</head>
<body>
	<p class="header"><a href="/config/">GitHome</a></p>
	<ul class="devices"> 
	
	<?php foreach (GitHome::getDevices() as $dev): ?>

	<li class="device border">
		<?=$dev->render() ?>
	</li>

	<?php endforeach;?>

	</ul>
	<div class="chart">
        <canvas id="chart"></canvas>
	</div>
</body>

</html>