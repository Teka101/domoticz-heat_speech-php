<?php

require 'config.php';
require 'classes/Domoticz.class.php';
require 'classes/Heating.class.php';

$hysteresis = 0.5;
$oldTemp = null;
$newTemp = null;
$oldHumidity = null;
$newHumidity = null;
$heating = new Heating();
$temperature = $heating->getCurrentTemperature();

$homeStatus = Domoticz::getHomeStatus();
if ($homeStatus)
{
	$oldTemp = $homeStatus->Temp;
	$oldHumidity = $homeStatus->Humidity;
}

echo "Temperature: $temperature<br>\n";
echo "Sending...<br>\n";
Domoticz::pushTemperature($temperature);
echo "<br>\n";
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

if ($newTemp != null && $newHumidity != null)
{
	if ($diffHum <= 10 && $diffHum <= 5)
	{
		echo "Sending home informations...<br>\n";
		Domoticz::pushHomeHumTemp($newHumidity, $newTemp);
		echo "<br>\n";

		$heaterStatus = Domoticz::getHeaterStatus();
		echo "Heater currentStatus: $heaterStatus<br>\n";
		if ($newTemp < ($temperature - $hysteresis))
		{
			if ($heaterStatus == 'Off')
			{
				echo "Turn on heater...<br>\n";
				Domoticz::setHeaterStatus('On');
			}
		}
		else if ($newTemp > ($temperature + $hysteresis))
		{
			if ($heaterStatus == 'On')
			{
				echo "Turn off heater...<br>\n";
				Domoticz::setHeaterStatus('Off');
			}
		}
	}
}
?>
