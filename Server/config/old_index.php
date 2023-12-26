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
<link rel="stylesheet" href="../index.css?filever=<?php echo filemtime('../index.css')?>">
<link rel="stylesheet" href="../main.css?filever=<?php echo filemtime('../main.css')?>">
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="stylesheet" href="oldconfig.css?filever=<?php echo filemtime('oldconfig.css')?>">
<script src="base64.js"></script>
<script src="config.js?filever=<?php echo filemtime('config.js')?>"></script>
</head>

<body>
<p class="header"><a href="/">SmartDom - Konfiguracja</a></p>

<table class="summarytable border">
	<tr>
		<td>
			<h3 class=text-center>Last 100 logs</h3>
		</td>
		<td>
			<h3 class=text-center>Firmware</h3>
		</td>
		<td>
			<h3 class=text-center>Execute command</h3>
		</td>
	</tr>
	<tr>
		<td>
			<div class="logcontainer">
				<?php
					foreach ($logs as $log)
					{
						echo sprintf('<p style="color:%s"><i>%s</i>: <b>%s</b> - %s</p>', $log['level'] == '0' ? "white" : ($log['level'] == '1' ? "yellow" : "#00ffff"), $log['date'], $log['device_id'], $log['data']);
					}
				?>
			</div>
		</td>
		<td>
			<table style="width: 100%">
				<tr>
					<th> Firmware </th>
					<th> Version </th>
				</tr>
				<?php
				foreach ($versions as $ver)
				{
					echo sprintf("<tr><td><p>%s</p></td><td><p>%s</p></td></tr>", $ver['firmware'], $ver['version']);
				}
				?>
				<tr style="height: 20px;"></tr>
				<form action="uploadfirmware.php" method="post" enctype="multipart/form-data">
				<tr>
					<td align="center" colspan="2">
						<?php
							echo sprintf('<label> Firmware: </label><select name="firmware" id="firmware">');
							foreach ($versions as $ver)
							{
								echo sprintf('<option value="%s" %s>%s</option>', $ver['firmware'], $ver['firmware'] == $dev->firmware ? "selected" : "",strtoupper($ver['firmware']));
							}
							echo "</select>";
						?>
					</td>
				</tr>

				<tr>
					<td align="center" colspan=1>
						<label> Version: </label> <input type="text" name="version" id="version">
					</td>
				</tr>
				<tr>
					<td align="center" colspan=2>
					<input type="file" name="fileToUpload" id="fileToUpload">
					</td>
				</tr>
				<tr>
					<td colspan=2 align="center">
						<input type="submit" value="Upload" name="submit">
					</td>
				</tr>
				</form>
				</div>
			</table>
		</td>
		<td>
			<div class="configbox">
				<input type="text" id="cliinput">
				<button id="cliclick">Execute</button>
				<p class="clioutput"> </p>
			</div>
		</td>
	</tr>
</table>

<div class="list border">

<h3 class=text-center> Boxes </h3>

<?php
foreach ($boxes as $box)
{
	
	echo sprintf('<button class="collapsible"><b>%s</b> - %s</button>', $box->id, $box->name);
	echo "<div class=\"content\">";

	echo sprintf('<label> Name: </label><input type=text id="name" value="%s"></input>', $box->name);
	echo sprintf('<label> Visible: </label><input type=checkbox id="visible" %s></input>',$box->visible ? "checked" : "");

	echo sprintf('
	<p> Code </p>
	<textarea class="config_txt" id=code>%s</textarea>
	',$box->code);

	echo sprintf('<button class="config_button" box-id="%s" onclick="save_box(this);">Save</button>', $box->id);

	echo "<ul class=devices >";
	
	echo sprintf('<li class="device box" box_id=%s>', $box->id);
	echo '<div class="data">';
	$box->evalCode($pdo);
	echo "</div></li>";
	
	echo "</ul>";

	echo "</div>";
}
?>

<button onclick=addBox()>Add new Box</button>

<h3 class=text-center>Devices</h3>

<?php

foreach ($devices as $dev)
{
	$dt = (new DateTime("now"))->diff(new DateTime($dev->lastreport));
	echo sprintf('
	<button class="collapsible">%s %s <b>%s</b> - %s</button>',  $dev->id, str_pad($dev->ip, 20), $dev->device_id, $dev->name);

	echo '<div class="content">';

	echo sprintf('<label> Type: </label><input type=text id="type" value="%s"></input>', $dev->type);
	echo sprintf('<label> Firmware: </label><select id="firmware">');
	foreach ($versions as $ver)
	{
		echo sprintf('<option value="%s" %s>%s</option>', $ver['firmware'], $ver['firmware'] == $dev->firmware ? "selected" : "",$ver['firmware']);
	}
	echo "</select>";
	echo sprintf('<label> Name: </label><input type=text id="name" value="%s"></input>',$dev->name);
	echo sprintf('
	<label> Visible: </label><input type=checkbox id="visible" %s %s></input> Data: <u>%s</u>
	',($dev->visible && !$dev->hidden) ? "checked" : "", $dev->hidden ? "disabled" : "", $dev->data ?? "");

	echo sprintf("<p>Last seen %ss, %sm, %sh, %sd, %sM ago.</p>", $dt->s,$dt->i,$dt->h,$dt->d,$dt->m);

	echo '<table class="devicetextareas"><tr>';
	echo sprintf('<td class="devicecodearea">
	<p> Custom code ran on report</p>
	<textarea class="config_txt" id=code>%s</textarea></td>
	', $dev->code);
	
	if ($dev->supportsSending)
	{
		echo sprintf('<td>
		<p> Content to report back to the device </p>
		<textarea class="config_txt" id="datatosend">%s</textarea></td>',
		$dev->command);
	}
	echo "</tr></table>";
	echo sprintf('<br><button class="config_button" device-id="%s" onclick="save_device(this);">Save</button>', $dev->device_id);

	echo "</div>";
}

?>

</div>
</body>
</html>