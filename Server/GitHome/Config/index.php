<?php
$versions = GitHome::$firmware->listFirmware(true);
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>GitHome - Config</title>
	<link rel="icon" type="image/png" href="/favicon.png">
	<link rel="stylesheet" href="/main.css">
	<link rel="stylesheet" href="<?GitPHP::static("config.css")?>">
	<script src="<?GitPHP::static("config.js")?>"></script>
</head>

<body>
<p class="header"><a href="/">GitHome - Config</a></p>

<table class="summarytable border">
	<tr>
		<td class="logs">
			<h2>Logs</h2>
			<div class="configbox">
				<div class="logcontainer">
					<?php foreach (GitHome::getLogs(100) as $log): ?>
					<p style="color:<?=$log['level'] == '0' ? "white" : ($log['level'] == '1' ? "yellow" : "#00ffff")?>"><i><?=$log['date']?></i>: <b><?=$log['device_id']?></b> - <?=$log['data']?></p>
					<?php endforeach; ?>
				</div>
			</div>
		</td>

		<td class="firmware">
			<h2>Firmware</h2>
			<div class="configbox firmwarecontainer">
				<table class="firmwaretable">
					<tr>
						<td>
				<p class="text-center"> <b> Current Firmware versions </b> </p>
				<table class="currentfirmware">
					<tr>
						<th> Name </th>
						<th> Version </th>
					</tr>
					<?php foreach ($versions as $ver): ?>
						<tr>
						<td> <?=$ver['name']?> </td>
						<td> <?=$ver['version']?> </td>
						</tr>
					<?php endforeach;?>
				</table>
						</td>
					</tr>

					<tr>
						<td>
				<form action="/config/uploadFirmware" method="post" enctype="multipart/form-data">
					<p class="text-center"> <b> Upload new Firmware </b> </p>

					<div class="form_element">
						<label> Name: </label>
						<select name="firmware" id="firmware">
							<option value=""> - </option>
							<?php foreach ($versions as $ver): ?>
							<option value="<?= $ver['name'] ?>"><?= strtoupper($ver['name']) ?> </option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="form_element">
						<label> New name: </label>
						<input type="text" name="name" id="name">
					</div>

					<div class="form_element">
						<label> Version: </label>
						<input type="text" name="version" id="version">
					</div>

					<div class="form_element">
						<label> Data: </label>
						<input type="file" name="fileToUpload" id="fileToUpload">
					</div>

					<div class="form_element text-center">
						<input type="submit" value="Upload">
					</div>
				</form>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>

<div class="list border">

	<h2 class=text-center>Devices</h2>

	<?php foreach (GitHome::getDevices() as $dev): ?>

		<button class="collapsible"> <b><?= $dev->name ?></b> - <?= $dev->id ?>, <i> Last seen: <?= $dev->lastReportTimestamp ?> from <?= $dev->lastReportIP ?> </i></button>

		<div class="content">

			<form action="/config/saveDevice" method="post" enctype="multipart/form-data">

				<input autocomplete="off" type="hidden" name="id" value="<?= $dev->id ?>"></input>

				<div class="form_element">
					<label> Name: </label>
					<input autocomplete="off" type=text name="name" value="<?=$dev->name?>"></input>
				</div>

				
				<div class="form_element">
					<label> Firmware: </label>
					<select autocomplete="off" name="firmware">
						<option value=""> - </option>
					<?php foreach ($versions as $firmware): ?>
						<option value="<?=$firmware['name']?>" <?=$firmware['name'] == $dev->firmware ? "selected" : ""?>><?=$firmware['name']?></option>
					<?php endforeach; ?>
					</select>
				</div>

				<div class="form_element">
					<label> Handler: </label>
					<select autocomplete="off" name="handler">
						<?php foreach (GitHome::$handlers as $handler): ?>
							<option value="<?=$handler?>" <?=$handler == $dev->handler ? "selected" : ""?>><?=$handler?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form_element">
					<label> Version: </label>
					<input autocomplete="off" type="text" name="version" id="version" value="<?=$dev->version?>">
				</div>

				<?php foreach ($dev->exportData() as $prop => $val): ?>
					<div class="form_element">
					<label for="<?=$prop?>"> <?=$prop ?>: </label>
					<textarea autocomplete="off" name="<?=$prop?>" id="<?=$prop?>"><?=$val?></textarea>
					</div>
				<?php endforeach; ?>
				
				<br>
				<br>
				<table class="device_buttons">
					<tr>
						<td>
							<input type="submit" value="Save">
						</td>
						<td>
							<button onclick='deleteDevice("<?= $dev->id ?>");'> Remove device </button>
						</td>
					</tr>
				</table>
			</form>

		</div>
	
	<?php endforeach; ?>
	<br>
	<br>
	<form action="/config/newDevice">
		<input class="collapsible" type="submit" value="Create new Device" />
	</form>
</div>
</body>
</html>