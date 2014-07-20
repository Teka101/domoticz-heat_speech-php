<?php
	require 'config.php';
	require 'classes/Calendar.class.php';
	require 'classes/Heating.class.php';
	require 'classes/SpeechRecognize.class.php';
	require 'classes/SpeechRecognize.' . SPEAK_LANGUAGE . '.class.php';
	require 'classes/PhilipsTv.class.php';

	header('Content-Type: text/xml; charset=UTF-8');
	echo '<?xml version="1.0" encoding="UTF-8" ?><responses>';
	if (array_key_exists('msg', $_GET))
	{
		$SR_SENTENCES = array();
		$SR_SENTENCES[] = array('text' => '^EAGGER_1$', 'action' => 'cbEagger');
		$SR_SENTENCES[] = array('text' => '^TO_LEAVE ([0-9]+) DURATION_DAY$', 'action' => 'cbLeaveHouseDay');
		$SR_SENTENCES[] = array('text' => '^TO_LEAVE ([0-9]+) DURATION_WEEK$', 'action' => 'cbLeaveHouseWeek');
		$SR_SENTENCES[] = array('text' => '^TO_LEAVE ([0-9]+) DURATION_MONTH$', 'action' => 'cbLeaveHouseMonth');
		$SR_SENTENCES[] = array('text' => '^TO_WATCH ([0-9]+)', 'action' => 'cbWatchTV');

		$sp = new SpeechRecognize($SR_SENTENCES, $SR_WORDS);
		if (!$sp->parseAndExecute($_GET['msg']))
			print '<tell>Je n\'ai pas compris votre demande...</tell>';
	}
	else
		print '<tell>At your command, my master.</tell>';
	echo '</responses>';
	exit (0);

function cbEagger()
{
	echo '<tell>Coucou les amis !</tell>';
}

function cbLeaveHouseDay($matches)
{
	cbLeaveHouse($matches[1] * 1);
}

function cbLeaveHouseWeek($matches)
{
	cbLeaveHouse($matches[1] * 7);
}

function cbLeaveHouseMonth($matches)
{
	cbLeaveHouse($matches[1] * 30);
}

function cbLeaveHouse($days)
{
	$heating = new Heating(true);
	$calendar = new Calendar();

	$temp = $heating->getBestTempatures($days);
	$dates = $calendar->getDatesFromNow($days);
	foreach ($dates as $date)
		$heating->setDateTemperature($date, $temp);

	echo "<tell>Température réglée à $temp degré pour une durée de $days jours.</tell>";
}

function cbWatchTV($matches)
{
	$channel = PhilipsTv::watch(PHILIPS_TV, $matches[1], 30);
	if ($channel)
		echo "<tell>Passage à la chaine $channel.</tell>";
	else
		echo "<tell>Oups cela ne fonctionne pas !</tell>";
}
?>
