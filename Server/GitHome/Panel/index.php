<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>GitHome</title>
	<link rel="stylesheet" href="/main.css">
	<link rel="icon" type="image/png" href="/favicon.png">
	
	<script src="<?php GitPHP::static("index.js") ?>"></script>
	<link rel="stylesheet" href="<?php GitPHP::static("index.css") ?>">

	<?php foreach (glob("../Handlers/CSS/*.css") as $css): ?>
		<link rel="stylesheet" href="<?php GitPHP::static($css) ?>">
	<?php endforeach;?>
	
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

</body>

</html>