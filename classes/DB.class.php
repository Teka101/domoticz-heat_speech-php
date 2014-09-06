<?php
class DataBase
{
	private $readWrite;
	private	$dbHandle;
	private $errorMsg;
	private $hasBuggedSubStr;
	
	public function	__construct($readWrite = false)
	{
		$this->readWrite = $readWrite;
		$this->errorMsg = null;
		$this->dbHandle = null;
		$this->connectToDataBase($readWrite);
		if ($readWrite)
			$this->createDataBase();
	}

	public function __destruct()
	{
		$this->errorMsg = null;
		$this->dbHandle = null;
	}
	
	private function connectToDataBase($readWrite)
	{
		try
		{
			$this->dbHandle = new PDO("sqlite:dbHeater.sqlite");
			$this->hasBuggedSubStr = ($this->dbHandle->getAttribute(PDO::ATTR_CLIENT_VERSION) == '3.2.8');
			$this->dbHandle->exec('PRAGMA journal_mode=OFF');
			$this->dbHandle->exec('PRAGMA temp_store=2');
		}
		catch (PDOException $e)
		{
			$this->errorMsg = $e->getMessage();
		}
	}

	private function createDataBase()
	{
		try
		{
    			$this->dbHandle->exec('CREATE TABLE heating(date INTEGER(8),temperature INTEGER(8),PRIMARY KEY(date))');
		}
		catch (PDOException $e)
		{
			$this->errorMsg = $e->getMessage();
		}
	}

	public function getData()
	{
		$query = $this->dbHandle->query('SELECT date, temperature FROM heating');
		if ($query)
			return $query->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
		return array();
	}

	public function setDateTemperature($date, $temperature)
	{
		if ($this->readWrite)
		{
			try
			{
				$query = $this->dbHandle->query("INSERT OR REPLACE INTO heating(date, temperature) VALUES($date, $temperature)");
				if ($query != null)
					return true;
			}
			catch (PDOException $e)
			{
				$this->errorMsg = $e->getMessage();
			}
		}
		return false;
	}

	public function deleteDate($date)
	{
		if ($this->readWrite)
		{
			try
			{
				$query = $this->dbHandle->query("DELETE FROM heating WHERE date=$date");
				if ($query != null)
					return true;
			}
			catch (PDOException $e)
			{
				$this->errorMsg = $e->getMessage();
			}
		}
		return false;
	}

	public function getLastError()
	{
		return $this->errorMsg;
	}
}
?>
