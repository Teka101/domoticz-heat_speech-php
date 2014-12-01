<?php
#Crontab:
#  */5 * * * * cd /home/pi/domoticz-heat_speech-php && /usr/bin/php update.php >/dev/null 2>/dev/null

require 'config.php';
require 'classes/Domoticz.class.php';
require 'classes/Heating.class.php';

$tempOffset = -1.0;
$hysteresis = 0.5;
$currentDate = new DateTime();
$oldDate = null;
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
	$oldDate = new DateTime($homeStatus->LastUpdate);
}

echo "Temperature: $temperature<br>\n";
echo "Sending...<br>\n";
Domoticz::pushTemperature(str_replace(',', '.', $temperature));
echo "<br>\n";
if (($hdl = popen('/home/pi/lol_dht22/loldht', 'r')))
{
	while (($str = fgets($hdl)))
		if (preg_match('/^Humidity = ([0-9\.]+) % Temperature = ([0-9\.]+) \*C/', $str, $m) == 1)
		{
			$newHumidity = $m[1];
			$newTemp = $m[2] + $tempOffset;
		}
	pclose($hdl);
}

$diffDate = ($oldDate == null ? 0 : abs($currentDate->getTimestamp() - $oldDate->getTimestamp()));
$diffHum = ($oldHumidity == null ? 0 : abs($oldHumidity - $newHumidity));
$diffTemp = ($oldTemp == null ? 0 : abs($oldTemp - $newTemp));

print "Date: " . $currentDate->format('c') . " [old=" . $oldDate->format('c') . " diff=$diffDate]<br>\n";
print "Home temperature: $newTemp [old=$oldTemp diff=$diffTemp]<br>\n";
print "Home humidity: $newHumidity [old=$oldHumidity diff=$diffHum]<br>\n";

if ($newTemp != null && $newHumidity != null)
{
	if ($diffDate >= 1200 || ($diffHum <= 15 && $diffTemp <= 5))
	{
		echo "Sending home informations...<br>\n";
		Domoticz::pushHomeHumTemp($newHumidity, str_replace(',', '.', $newTemp));
		echo "<br>\n";

		$heaterStatus = Domoticz::getHeaterStatus();
		$heaterStatus = str_replace(', Level: 100 %', '', $heaterStatus);
		echo "Heater currentStatus: $heaterStatus<br>\n";
		if ($newTemp <= ($temperature - $hysteresis))
		{
			//if ($heaterStatus == 'Off')
			{
				echo "Turn on heater...<br>\n";
				Domoticz::setHeaterStatus('On');
			}
		}
		else if ($newTemp >= ($temperature + $hysteresis))
		{
			//if ($heaterStatus == 'On')
			{
				echo "Turn off heater...<br>\n";
				Domoticz::setHeaterStatus('Off');
			}
		}
	}
}
?>
