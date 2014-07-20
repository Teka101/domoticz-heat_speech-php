<?php

require 'config.php';
require 'classes/Domoticz.class.php';
require 'classes/Heating.class.php';

$heating = new Heating();
$temperature = $heating->getCurrentTemperature();

echo "Temperature: $temperature<br>\n";
echo "Sending...<br>\n";
Domoticz::pushTemperature($temperature);

?>
