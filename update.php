<?php

require 'config.php';
require 'classes/Domoticz.class.php';
require 'classes/Heating.class.php';

$oldTemp = null;
$newTemp = null;
$oldHumidity = null;
$newHumidity = null;

if (($hdl = popen('/bin/cat /home/pi/DHT22.txt', 'r')))
{
	while (($str = fgets($hdl)))
		if (preg_match('/^Humidity = ([0-9\.]+) % Temperature = ([0-9\.]+) \*C/', $str, $m) == 1)
		{
			$newHumidity = $m[1];
			$newTemp = $m[2];
		}
	pclose($hdl);
}

$diffHum = ($oldHumidity == null ? 0 : abs($oldHumidity - $newHumidity));
$diffTemp = ($oldTemp == null ? 0 : abs($oldTemp - $newTemp));

print "Home temperature: $newTemp [old=$oldTemp diff=$diffTemp]<br>\n";
print "Home humidity: $newHumidity [old=$oldHumidity diff=$diffHum]<br>\n";
if (($diffHum <= 10 && $diffHum <= 5)
	&& ($newTemp != null && $newHumidity != null))
{
	echo "Sending home informations...<br>\n";
	Domoticz::pushHomeHumTemp($newHumidity, $newTemp);
	echo "<br>\n";
}

$heating = new Heating();
$temperature = $heating->getCurrentTemperature();

echo "Temperature: $temperature<br>\n";
echo "Sending...<br>\n";
Domoticz::pushTemperature($temperature);

?>
