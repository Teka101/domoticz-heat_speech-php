<?php

require 'classes/DB.class.php';

class Heating
{
	private $db;
	private $typeDefault;
	private $types;
	private	$temps;
	private $tempValues;

	public function	__construct($readWrite = false)
	{
		$this->typeDefault = 21;
		$this->types = [ '12', '19', '20', '21', '22' ];
		$this->db = new DataBase($readWrite);
		$this->load();
	}

	private function getBestTempatures($nbDays)
	{
		if ($nbDays > 8)
			return 12;
		return 19;
	}

	private function load()
	{
		$this->temps = $this->db->getData();
		$this->tempValues = array();
		foreach ($this->temps as $k => &$v)
		{
			$type = $v[0];
			if (!array_key_exists($type, $this->tempValues))
				$this->tempValues[$type] = 1;
			else
				$this->tempValues[$type]++;
		}
	}

	public function getRawCurrentTemperature()
	{
		$currentDate = localtime(time(), true);
		$now = mktime(0, 0, 0, $currentDate['tm_mon'] + 1, $currentDate['tm_mday'], 1900 + $currentDate['tm_year']);
		$temperature = $this->getDateType($now);
		if (!$temperature)
			$temperature = $this->getDefaultType();
		return $temperature;
	}

	public function getCurrentTemperature()
	{
		$currentDate = localtime(time(), true);
		$hh = $currentDate['tm_hour'];
		$wDay = $currentDate['tm_wday'];
		$now = mktime(0, 0, 0, $currentDate['tm_mon'] + 1, $currentDate['tm_mday'], 1900 + $currentDate['tm_year']);
		$temperature = $this->getDateType($now);
		if (!$temperature)
			$temperature = $this->getDefaultType();
		if ($temperature >= 19)
		{
			if ($wDay == 0 || $wDay == 6)
			{
				if ($hh <= 9 || $hh >= 21)
					$temperature--;
				else if ($hh <= 18)
					$temperature -= 0.5;
			}
			else
			{
				if ($hh <= 5 || $hh >= 21)
					$temperature--;
				else if ($hh <= 18)
					$temperature -= 0.5;
			}
		}
		return $temperature;
	}

	public function addToCurrentTemperature($offset)
	{
		$newTemp = $this->getRawCurrentTemperature() + $offset;
		if ($this->setCurrentTemperature($newTemp))
			return $newTemp;
		return false;
	}

	public function setCurrentTemperature($temperature)
	{
		$currentDate = localtime(time(), true);
		$now = mktime(0, 0, 0, $currentDate['tm_mon'] + 1, $currentDate['tm_mday'], 1900 + $currentDate['tm_year']);
		return $this->setDateTemperature($now, $temperature);
	}

	public function setBestTempratureForDates($dates)
	{
		$days = count($dates);
		$temp = $this->getBestTempatures($days);
		$lastDate = end($dates);
		foreach ($dates as $date)
			if ($date == $lastDate && $temp == $this->types[0])
				$this->setDateTemperature($date, $this->types[1]);
			else
				$this->setDateTemperature($date, $temp);
		return $temp;
	}

	public function setDateTemperature($date, $temperature)
	{
		if (!in_array($temperature, $this->types))
			return false;
		if ($temperature == $this->typeDefault)
			return $this->db->deleteDate($date);
		return $this->db->setDateTemperature($date, $temperature);
	}

	public function getTypes()
	{
		return $this->types;
	}

	public function getDefaultType()
	{
		return $this->typeDefault;
	}

	public function getDateType($date)
	{
		if (array_key_exists($date, $this->temps))
		{
			$ar = $this->temps[$date];
			return $ar[0];
		}
		return false;
	}

	public function getNumberType($type)
	{
		if (array_key_exists($type, $this->tempValues))
			return $this->tempValues[$type];
		return 0;
	}
}
?>
