<?php
class PhilipsTv
{
	static public function watch($tvIp, $channelPreset, $volume = null, $activeAmbilight)
	{
		$channels = PhilipsTv::getChannels($tvIp);
		if ($channels)
			foreach ($channels as $channelId => &$channel)
				if ($channel->preset == $channelPreset)
				{
					$currentChannelId = PhilipsTv::getCurrentChannel($tvIp);
					if ($channelId != $currentChannelId)
						if (!PhilipsTv::setChannel($tvIp, $channelId))
							return null;
					if ($volume)
						PhilipsTv::setAudioVolume($tvIp, $volume);
					PhilipsTv::setAmbilight($tvIp, $activeAmbilight);
					return $channel->name;
				}
		return null;
	}

	static private function setAmbilight($tvIp, $activeAmbilight)
	{
		PhilipsTv::doRequest($tvIp, '/1/ambilight/mode', '{"current": "internal"}');
		$ambilightJson = PhilipsTv::doRequest($tvIp, '/1/ambilight/processed');
		if ($ambilightJson)
		{
			$ambilight = json_decode($ambilightJson);
			if (!$ambilight)
				return false;

			$ambilightIsEmpty = (PhilipsTv::ambilightIsEmpty($ambilight->layer1->left)
						&& PhilipsTv::ambilightIsEmpty($ambilight->layer1->top)
						&& PhilipsTv::ambilightIsEmpty($ambilight->layer1->right)
						&& PhilipsTv::ambilightIsEmpty($ambilight->layer1->bottom));

			if ($activeAmbilight)
			{
				if ($ambilightIsEmpty)
				{
					PhilipsTv::doRequest($tvIp, '/1/input/key', '{"key": "AmbilightOnOff"}');
					sleep(1);
					PhilipsTv::doRequest($tvIp, '/1/input/key', '{"key": "CursorDown"}');
					PhilipsTv::doRequest($tvIp, '/1/input/key', '{"key": "Confirm"}');
				}
			}
			else
			{
				if (!$ambilightIsEmpty)
				{
					PhilipsTv::doRequest($tvIp, '/1/input/key', '{"key": "AmbilightOnOff"}');
					sleep(1);
					PhilipsTv::doRequest($tvIp, '/1/input/key', '{"key": "CursorUp"}');
					PhilipsTv::doRequest($tvIp, '/1/input/key', '{"key": "Confirm"}');
				}
			}
			return true;
		}
		return false;
	}

	static private function ambilightIsEmpty($ambiLeftRightTopBottom)
	{
		foreach ($ambiLeftRightTopBottom as $ambiId => &$ambiRgb)
			if ($ambiRgb->r != 0 || $ambiRgb->g != 0 || $ambiRgb->b != 0)
				return false;
		return true;
	}

	static private function setAudioVolume($tvIp, $volume)
	{
		$htmlResponse = PhilipsTv::doRequest($tvIp, '/1/audio/volume', "{\"muted\": false, \"current\": $volume}");
		if ($htmlResponse)
			return true;
		return false;
	}

	static private function getCurrentChannel($tvIp)
	{
		$jsonChannel = PhilipsTv::doRequest($tvIp, '/1/channels/current');
		if ($jsonChannel)
		{
			$json = json_decode($jsonChannel);
			return $json->id;
		}
		return null;
	}

	static private function setChannel($tvIp, $channelId)
	{
		$htmlResponse = PhilipsTv::doRequest($tvIp, '/1/channels/current', "{\"id\": \"$channelId\"}");
		if ($htmlResponse)
			return true;
		return false;
	}

	static private function getChannels($tvIp)
	{
		$jsonChannels = PhilipsTv::doRequest($tvIp, '/1/channels');
		if ($jsonChannels)
			return json_decode($jsonChannels);
		return null;
	}

	static private function doRequest($tvIp, $url, $postData = null)
	{
		$ch = curl_init("http://$tvIp:1925$url");
		if ($ch)
		{
			if ($postData)
			{
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			}
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1);
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		}
		return null;
	}
}
?>
