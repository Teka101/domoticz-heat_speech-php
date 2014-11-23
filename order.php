<?php
	require_once 'config.php';
	require_once 'classes/Calendar.class.php';
	require_once 'classes/Domoticz.class.php';
	require_once 'classes/Heating.class.php';
	require_once 'classes/SpeechRecognize.class.php';
	require_once 'classes/SpeechRecognize.' . SPEAK_LANGUAGE . '.class.php';
	require_once 'classes/PhilipsTv.class.php';
	require_once 'classes/TvPrograms.class.php';

	$isJson = array_key_exists('json', $_GET);
	if ($isJson)
	{
		header('Content-Type: application/json; charset=UTF-8');
		echo '{';
	}
	else
	{
		header('Content-Type: text/xml; charset=UTF-8');
		echo '<?xml version="1.0" encoding="UTF-8" ?><responses>';
	}
	if (array_key_exists('msg', $_GET))
	{
		$SR_SENTENCES = array();
		$SR_SENTENCES[] = array('text' => '^EAGGER_1$', 'action' => 'cbEagger');
		$SR_SENTENCES[] = array('text' => '^TO_LEAVE$', 'action' => 'cbLeaveHouse');
		$SR_SENTENCES[] = array('text' => '^TO_LEAVE HOUSE$', 'action' => 'cbLeaveHouse');
		$SR_SENTENCES[] = array('text' => '^TO_LEAVE ([0-9]+) DURATION_DAY$', 'action' => 'cbLeaveHouseDay');
		$SR_SENTENCES[] = array('text' => '^TO_LEAVE ([0-9]+) DURATION_WEEK$', 'action' => 'cbLeaveHouseWeek');
		$SR_SENTENCES[] = array('text' => '^TO_LEAVE ([0-9]+) DURATION_MONTH$', 'action' => 'cbLeaveHouseMonth');
		$SR_SENTENCES[] = array('text' => '^TO_WATCH .*MOVIE ([0-9]+)$', 'action' => 'cbWatchMovie');
		$SR_SENTENCES[] = array('text' => '^TO_WATCH ([0-9]+)$', 'action' => 'cbWatchTV');
		$SR_SENTENCES[] = array('text' => '^TO_DO HEATING ([0-9]+)$', 'action' => 'cbHeatingAt');
		$SR_SENTENCES[] = array('text' => '^COLD$', 'action' => 'cbHeatingMore');
		$SR_SENTENCES[] = array('text' => '^HEAT$', 'action' => 'cbHeatingLess');
		$SR_SENTENCES[] = array('text' => '^HOW HOUSE$', 'action' => 'cbHowHouse');
		$SR_SENTENCES[] = array('text' => '^WHAT NIGHT', 'action' => 'cbTvProgramTonight');

		$sp = new SpeechRecognize($SR_SENTENCES, $SR_WORDS);
		if (!$sp->parseAndExecute($_GET['msg']))
			tell('Je n\'ai pas compris votre demande...');
	}
	else
		tell('At your command, my master.');
	if ($isJson)
		echo '}';
	else
		echo '</responses>';
	exit (0);

function tell($msg)
{
	global $isJson;

	if ($isJson)
		print '"tell:" "' . $msg . '"';
	else
		print '<tell>' . $msg . '</tell>';
}

function cbEagger()
{
	tell('Coucou les amis !');
}

function cbLeaveHouseDay($matches)
{
	cbLeaveHouseHeat($matches[1] * 1);
}

function cbLeaveHouseWeek($matches)
{
	cbLeaveHouseHeat($matches[1] * 7);
}

function cbLeaveHouseMonth($matches)
{
	cbLeaveHouseHeat($matches[1] * 30);
}

function cbLeaveHouseHeat($days)
{
	$heating = new Heating(true);
	$calendar = new Calendar();

	$dates = $calendar->getDatesFromNow($days);
	$temp = $heating->setBestTempratureForDates($dates);
	tell("Température réglée à $temp degré pour une durée de $days jours.");
}

function cbLeaveHouse($matches)
{
	$sent = '';
	if (PhilipsTv::stopTv(PHILIPS_TV))
		$sent .= 'J\'ai éteins la télévision. ';
	$sent .= 'Au revoir.';
	tell($sent);
}

function cbWatchMovie($matches)
{
	$channel = PhilipsTv::watch(PHILIPS_TV, $matches[1], 30, true);
	if ($channel)
		tell("Passage à la chaine $channel.");
	else
		tell("La télé n'est pas allumée.");
}

function cbWatchTV($matches)
{
	$channel = PhilipsTv::watch(PHILIPS_TV, $matches[1], 25, false);
	if ($channel)
		tell("Passage à la chaine $channel.");
	else
		tell("La télé n'est pas allumée.");
}

function cbHeatingAt($matches)
{
	$heating = new Heating(true);
	$newTemp = $matches[1];
	if ($heating->setCurrentTemperature($newTemp))
		tell("Le chauffage est régle à $newTemp degré.");
	else
		tell("Le chauffage reste réglé à " . $heating->getCurrentTemperature() . " degré car la température demandée n'est pas connue.");
}

function cbHeatingMore($matches)
{
	cbHeating(1);
}

function cbHeatingLess($matches)
{
	cbHeating(-1);
}

function cbHeating($offset)
{
	$heating = new Heating(true);
	$newTemp = $heating->addToCurrentTemperature($offset);

	if (is_numeric($newTemp))
		tell("Le chauffage est réglé à $newTemp degré.");
	else if ($offset > 0)
		tell("Le chauffage est déjà au maximum !");
	else if ($offset < 0)
		tell("Le chauffage est déjà au minimum !");
}

function cbHowHouse($matches)
{
	$temp = Domoticz::getHomeTemperature();
	$locale = localeconv();
	if ($locale)
		$temp = number_format($temp, (is_float($temp) ? 1 : 0), $locale['decimal_point'], $locale['thousands_sep']);
	if ($temp)
		tell("La température ambiante est de $temp degré.");
	else
		tell("Impossible de récupérer la température...");
}

function cbTvProgramTonight($matches)
{
	$programs = TvPrograms::whatTonight();
	if ($programs)
	{
		$tellMe = '';
		foreach ($programs as $programChannel => $program)
			$tellMe .= "Sur $programChannel, il y a " . $program['title'] . ". ";
		tell("Voici le programme télé: $tellMe");
	}
	else
		tell("Impossible d'avoir le programme télé...");
}
?>
