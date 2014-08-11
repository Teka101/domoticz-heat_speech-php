<?php
class TvPrograms
{
	static public function whatTonight()
	{
		return TvPrograms::getPrograms(array('TF1','France 2','France 3','France 4','M6', 'W9', 'NT1'), 2100);
	}

	static private function getPrograms($channels, $hourBegin)
	{
		$emissions = TvPrograms::getProgramsRaw();
		if ($emissions)
		{
			$programs = array();
			foreach ($emissions as $emission)
			{
				$item = array();
				list($item['channel'], $item['hour'], $item['title']) = explode(' | ', $emission->title);

				if (in_array($item['channel'], $channels))
				{
					$hour = intval(str_replace(':', '', $item['hour']));
					if ($hour < $hourBegin)
					{
						$item['category'] = '' . $emission->category;
						$item['description'] = strip_tags('' . $emission->description);
						$programs[$item['channel']] = $item;
					}
				}
			}
			return $programs;
		}
		return null;
	}

	static private function getProgramsRaw()
	{
		$dateNow = date('Y-m-d');
		$ch = curl_init("http://webnext.fr/epg_cache/programme-tv-rss_$dateNow.xml");
		if ($ch)
		{
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			$response = curl_exec($ch);
			curl_close($ch);
			$xml = simplexml_load_string($response);
			if ($xml)
				return $xml->xpath('/rss/channel/item');
		}
		return null;
	}
}
?>
