<?php
class PhilipsTv
{
	static public function watch($tvIp, $channelPreset, $volume = null)
	{
		$channels = PhilipsTv::getChannels($tvIp);
		if ($channels)
			foreach ($channels as $channelId => &$channel)
				if ($channel->preset == $channelPreset)
				{
					if (PhilipsTv::setChannel($tvIp, $channelId))
					{
						if ($volume)
							PhilipsTv::setAudioVolume($tvIp, $volume);
						PhilipsTv::setAmbilight($tvIp, 'expert');
						return $channel->name;
					}
					break;
				}
		return null;
	}

	static private function setAmbilight($tvIp, $mode)
	{
		$ch = curl_init("http://$tvIp:1925/1/ambilight/cached");
		if ($ch)
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"r\": 255, \"g\": 255, \"b\": 255}");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			$htmlResponse = curl_exec($ch);
			curl_close($ch);
		}
		$ch = curl_init("http://$tvIp:1925/1/ambilight/mode");
		if ($ch)
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"current\": \"$mode\"}");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			$htmlResponse = curl_exec($ch);
			curl_close($ch);
			return true;
		}
		return false;
	}

	static private function setAudioVolume($tvIp, $volume)
	{
		$ch = curl_init("http://$tvIp:1925/1/audio/volume");
		if ($ch)
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"muted\": false, \"current\": $volume}");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			$htmlResponse = curl_exec($ch);
			curl_close($ch);
			return true;
		}
		return false;
	}

	static private function setChannel($tvIp, $channelId)
	{
return true;
		$ch = curl_init("http://$tvIp:1925/1/channels/current");
		if ($ch)
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"id\": \"$channelId\"}");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			$htmlResponse = curl_exec($ch);
			curl_close($ch);
			return true;
		}
		return false;
	}

	static private function getChannels($tvIp)
	{
		$ch = curl_init("http://$tvIp:1925/1/channels");
		if ($ch)
		{
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			$jsonChannels = curl_exec($ch);
			curl_close($ch);
			if ($jsonChannels)
				return json_decode($jsonChannels);
		}
		return null;
	}
}
?>
