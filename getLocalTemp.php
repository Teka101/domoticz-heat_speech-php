<?php

require 'config.php';
require 'classes/Domoticz.class.php';

header('Content-type: application/json');
$homeStatus = Domoticz::getHomeStatus();
if ($homeStatus)
	print json_encode($homeStatus);
?>
