<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>GitHome</title>
	<link rel="icon" type="image/png" href="/favicon.png">
	<link rel="stylesheet" href="<?= GitPHP::static("/main.css") ?>">
	<script src="<?GitPHP::static("/GitHome.js")?>"></script>
	<link rel="stylesheet" href="<?php $this->static("panel.css") ?>">

	<?php foreach (glob("../Handlers/CSS/*.css") as $css): ?>
		<link rel="stylesheet" href="<?php $this->static($css) ?>">
	<?php endforeach;?>

</head>
<body>
	<p class="header"><a href="/config/">GitHome</a></p>
	<ul class="devices"> 
	
	<?php foreach ($this->filterDevices() as $dev): ?>

	<li class="device border"><?=$dev->render()?></li>

	<?php endforeach;?>

	</ul>

</body>

</html>