<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once "../api/common.php";
require_once "../api/pdo_connect.php";

$logs = get_logs(100, PHP_INT_MAX);
$boxes = get_boxes();
$devices = get_devices();
$versions = get_versions();
?>

<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<title>SmartDom - Konfiguracja</title>
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="stylesheet" href="/main.css?filever=<?php echo filemtime('../main.css')?>">
<script src="base64.js"></script>
<script src="config.js?filever=<?php echo filemtime('config.js')?>"></script>
<link rel="stylesheet" href="config.css?filever=<?php echo filemtime('config.css')?>">

<link rel="stylesheet" href="prism.css">
<script src="prism.js"></script>
</head>

<body>
<p class="header"><a href="/">SmartDom - Konfiguracja</a></p>

<table class="summarytable border">
	<tr>
		<td>
			<h2>Logs</h2>
		</td>
		<td>
			<h2>Firmware</h2>
		</td>
		<td>
			<h2>CLI</h2>
		</td>
	</tr>
	<tr>
		<td>
			<div class="configbox logcontainer">
				<?php foreach ($logs as $log): ?>
					<p style="color:<?=$log['level'] == '0' ? "white" : ($log['level'] == '1' ? "yellow" : "#00ffff")?>"><i><?=$log['date']?></i>: <b><?=$log['device_id']?></b> - <?=$log['data']?></p>
				<?php endforeach; ?>
			</div>
		</td>
		<td>
			<div class="configbox firmwarecontainer">
				<table>
					<tr>
						<th> Firmware </th>
						<th> Version </th>
					</tr>
					<?php foreach ($versions as $ver): ?>
						<tr>
						<td> <p><?=$ver['firmware']?></p> </td>
						<td> <p><?=$ver['version']?></p> </td>
						</tr>
					<?php endforeach;?>
				</table>
				<form action="uploadfirmware.php" method="post" enctype="multipart/form-data">
					<label> Firmware: </label>

					<select name="firmware" id="firmware">
					<?php foreach ($versions as $ver): ?>
						<option value="<?= $ver['firmware'] ?>" <?= $ver['firmware'] == $dev->firmware ? "selected" : "" ?>><?= strtoupper($ver['firmware']) ?></option>
					<?php endforeach; ?>

					</select>
					</br>
					<label> Version: </label>
					<input type="text" name="version" id="version">
					
					</br>	
					<input type="file" name="fileToUpload" id="fileToUpload">
					</br>	
					<input type="submit" value="Upload" name="submit">

				</form>
				</div>
		</td>
		<td>
			<div class="configbox commandcontainer">
				<div class="cli">
					<input type="text" id="cliinput">
					<button id="cliclick">Execute</button>
				</div>
				<p class="clioutput"> </p>
			</div>
		</td>
	</tr>
</table>

<div class="list border">

	<h2 class=text-center> Boxes </h2>

	<?php foreach ($boxes as $box): ?>
		<button class="collapsible"><b><?= $box->id ?></b> - <?= $box->name ?> </button>

		<div class="content">
			<label> Name: </label><input type=text id="name" value=" <?= $box->name ?>"></input>
			<label> Visible: </label><input type=checkbox id="visible" <?= $box->visible ? "checked" : "" ?> ></input>

			<p> Code </p>
			<div class=codeeditor>
			<textarea autocomplete="off" spellcheck="false" id=code onpaste="update(this); sync_scroll(this);" oninput="update(this); sync_scroll(this);" onscroll="sync_scroll(this);" onkeydown="check_tab(this, event);"><?=$box->code?></textarea><pre id="highlighting" aria-hidden="true"><code class="language-php" id="highlighting-content"></code></pre>
			</div>
			<button class="config_button" box-id="<?= $box->id ?>" onclick="save_box(this);">Save</button>

			<ul class=devices>
			
			<li class="device box border" box_id=<?= $box->id ?>>
			<div class="data">

			<?php $box->evalCode($pdo) ?>
			</div>
			</li>
			
			</ul>
		</div>
	<?php endforeach; ?>

	<button onclick=addBox()>Add new Box</button>

	<h2 class=text-center>Devices</h2>

	<?php foreach ($devices as $dev): ?>

		<button class="collapsible"><?=$dev->id?> <?=str_pad($dev->ip, 20)?> <b><?= $dev->device_id?></b> - <?=$dev->name?></button>

		<div class="content">

		<label> Type: </label> <input type=text id="type" value="<?=$dev->type?>"></input>
		<label> Firmware: </label>
		<select id="firmware">
			<?php foreach ($versions as $ver): ?>
				<option value="<?=$ver['firmware']?>" <?=$ver['firmware'] == $dev->firmware ? "selected" : ""?>><?=$ver['firmware']?></option>
			<?php endforeach; ?>
		</select>

		<label> Name: </label> <input type=text id="name" value="<?=$dev->name?>"></input>
		<label> Visible: </label><input type=checkbox id="visible" <?=($dev->visible && !$dev->hidden) ? "checked" : ""?> <?=$dev->hidden ? "disabled" : ""?>></input>
		</br>
		</br>
		<span> Data: <u><?=$dev->data?></u> </span>
		</br>
		<span>Last seen: <?=$dev->lastreport?></span>

		<table class="devicetextareas">
			<tr>
				<td class="devicecodearea">
					<p> Code </p>
					<div class=codeeditor>
						<textarea autocomplete="off" spellcheck="false" id=code onpaste="update(this); sync_scroll(this);" oninput="update(this); sync_scroll(this);" onscroll="sync_scroll(this);" onkeydown="check_tab(this, event);"><?=$dev->code?></textarea><pre id="highlighting" aria-hidden="true"><code class="language-php" id="highlighting-content"></code></pre>
					</div>
				</td>
			<?php if ($dev->supportsSending): ?>
				<td>
					<p> Content to report back to the device </p>
					<textarea class="config_txt" id="datatosend"><?=$dev->command?></textarea>
				</td>
			<?php endif; ?>
			</tr>
	</table>
		<button class="config_button" device-id="<?=$dev->device_id?>" onclick="save_device(this);">Save</button>
		</div>

		<?php endforeach; ?>

</div>
</body>
</html>