<?php

class Domoticz
{
	static public function pushTemperature($temp)
	{
		$ch = curl_init(DOMOTICZ_SERVER . '/json.htm?type=command&param=udevice&idx=' . DOMOTICZ_HEATING_IDX . '&nvalue=0&svalue=' . $temp);
		if ($ch)
		{
			curl_exec($ch);
			curl_close($ch);
		}
	}
}
?>
