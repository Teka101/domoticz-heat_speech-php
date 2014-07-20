<?php
	require 'config.php';
	require 'classes/Heating.class.php';
	require 'classes/SpeechRecognize.class.php';

	//$heating = new Heating();

	//header('Content-Type: text/xml; charset=UTF-8');
	echo '<?xml version="1.0" encoding="UTF-8" ?><responses>';
	if (array_key_exists('msg', $_GET))
	{
		$sp = new SpeechRecognize(null, null);
		$sp->parseAndExecute($_GET['msg']);
	}
	else
		print '<tell>At your command, my master.</tell>';
	echo '</responses>';
?>
