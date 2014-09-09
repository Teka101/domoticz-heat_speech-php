<?php

class Domoticz
{
	static public function pushHomeHumTemp($hum, $temp)
	{
		$ch = curl_init(DOMOTICZ_SERVER . '/json.htm?type=command&param=udevice&idx=' . DOMOTICZ_HEAT_HOME_IDX . '&nvalue=0&svalue=' . $temp . ';' . $hum . ';2');
		if ($ch)
		{
			curl_exec($ch);
			curl_close($ch);
		}
	}

	static public function pushTemperature($temp)
	{
		$ch = curl_init(DOMOTICZ_SERVER . '/json.htm?type=command&param=udevice&idx=' . DOMOTICZ_HEATING_IDX . '&nvalue=0&svalue=' . $temp);
		if ($ch)
		{
			curl_exec($ch);
			curl_close($ch);
		}
	}

	static public function getHomeStatus()
	{
		return Domoticz::getDeviceResult(DOMOTICZ_HEAT_HOME_IDX);
	}

	static public function getHomeTemperature()
	{
		$r = Domoticz::getDeviceResult(DOMOTICZ_HEAT_HOME_IDX);
		if ($r)
			return $r->Temp;
		return false;
	}

	static public function setHeaterStatus($status)
	{
		$ch = curl_init(DOMOTICZ_SERVER . '/json.htm?type=command&param=param=switchscene&idx=' . DOMOTICZ_HEAT_HOME_IDX . '&switchcmd=' . $status);
		if ($ch)
		{
			curl_exec($ch);
			curl_close($ch);
		}
	}

	static public function getHeaterStatus()
	{
		$r = Domoticz::getDeviceResult(DOMOTICZ_HEATER_IDX);
		if ($r)
			return $r->Data;
		return null;
	}

	static private function getDeviceResult($deviceIdx)
	{
		$ch = curl_init(DOMOTICZ_SERVER . '/json.htm?type=devices&rid=' . $deviceIdx);
		if ($ch)
		{
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$response = curl_exec($ch);
			curl_close($ch);
			if ($response)
			{
				$json = json_decode($response);
				if (is_array($json->result))
					return $json->result[0];
				return $json->result;
			}
		}
		return null;
	}
}
?>
