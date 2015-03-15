<?php
	require 'config.php';
	require 'classes/Calendar.class.php';
	require 'classes/Heating.class.php';

	$calendar = new Calendar();
	if (array_key_exists('action', $_GET))
	{
		$heating = new Heating(true);

		header('Content-Type: text/xml; charset=UTF-8');
		echo '<?xml version="1.0" encoding="UTF-8" ?><responses>';
		if (array_key_exists('addDay', $_GET))
		{
			$date = $_GET['addDay'];
			$temp = $_GET['type'];
			if ($heating->setDateTemperature($date, $temp))
				echo '<style id="d' . $date . '">type' . $temp . '</style>';
		}
		echo '</responses>';
		exit(0);
	}

	header('Content-Type: text/html; charset=UTF-8');
	$heating = new Heating();
	$calYear = date('Y');
	if (array_key_exists('year', $_GET))
		$calYear = $_GET['year'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <title>Domoticz control</title>
 <meta name="viewport" content="width=device-width,initial-scale=1">
 <link rel="stylesheet" type="text/css" href="web/bootstrap-3.2.0-dist/css/bootstrap.min.css">
 <link rel="stylesheet" type="text/css" href="web/bootstrap-3.2.0-dist/css/bootstrap-theme.min.css">
 <link rel="stylesheet" type="text/css" href="web/style.css">
 <script type="text/javascript" src="web/jquery-2.1.1.min.js"></script>
</head>
<body>
 <div class="center-block text-center" style="padding-top: 10px">
  <form class="form-inline" role="form">
   <div class="input-group">
    <span class="input-group-addon">Speak</span>
    <input id="speechText" type="text" placeholder="Speak and order !" class="form-control" style="width: 265px">
    <span class="input-group-addon">
     <span id="speechButton" class="glyphicon glyphicon-ban-circle speech-mic"></span>
    </span>
   </div>
   <button id="orderButton" type="button" class="btn btn-default">Execute</button>
  </form>
  <div class="container" style="padding-top: 20px">
   Assign temperature: 
   <select id="typeDay">
<?php
	foreach ($heating->getTypes() as $type)
		echo "<option>$type</option>";
?>
   </select>
  </div>
 </div>
 <div class="container-fluid">
  <div class="row">
   <div class="col-xs-6 text-left"><a class="btn btn-default" href="<?= $_SERVER['PHP_SELF'] ?>?year=<?= ($calYear - 1) ?>">&lt;&lt;</a></div>
   <div class="col-xs-6 text-right"><a class="btn btn-default" href="<?= $_SERVER['PHP_SELF'] ?>?year=<?= ($calYear + 1) ?>">&gt;&gt;</a></div>
  </div>
 </div>
 <div align="center">
  <table class="table table-bordered table-condensed text-center" style="width: auto">
<?php

	$begin = mktime(0, 1, 0, 1, 1, $calYear);
	$end = mktime(0, 1, 0, 1, 1, $calYear + 1);
	$ret = $calendar->getDates($begin, $end);
	$nbMonths = count($ret);
	echo '<thead><tr>';
	for ($posMonth = 0; $posMonth < $nbMonths; $posMonth++)
		echo '<th>' . $ret[$posMonth][0] . '</th>';
	echo '</tr></thead>';
	echo '<tbody>';
	for ($posDay = 1; $posDay <= 31; $posDay++)
	{
		echo '<tr>';
		for ($posMonth = 0; $posMonth < $nbMonths; $posMonth++)
		{
			echo '<td';
			if (array_key_exists($posDay, $ret[$posMonth]))
			{
				$cDate = $ret[$posMonth]["time-$posDay"];
				$cText = $ret[$posMonth][$posDay];
				$cTemp;
				if (($typeDate = $heating->getDateType($cDate)))
				{
					$cType = 'type' . $typeDate;
					$cTemp = $typeDate;
				}
				else if ($calendar->isWeekend($cDate))
				{
					$cType = 'weekend';
					$cTemp = $heating->getDefaultType();
				}
				else
				{
					$cType = '';
					$cTemp = $heating->getDefaultType();
				}
				echo ' id="d'.$cDate.'" class="calDate ' . $cType . '" title="' . $cTemp . '°">' . $cText;
			}
			else
				echo ' style="border: none">&nbsp;';
			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody>';
?>
  </table>
 </div>
 <script>
var recognition = null;

$(function()
{
	$('.calDate').click(function()
		{
			var calDate = $(this).attr('id').substring(1);
			var typeClass = $(this).attr('class').replace('type', '');
			var newTypeClass = $('#typeDay option:selected').text();

			if (typeClass != newTypeClass)
		                $.ajax({
						url: '<?= $_SERVER['PHP_SELF'] ?>',
						type: 'GET',
						data: 'action=js&addDay=' + calDate + '&type=' + newTypeClass,
						dataType: 'xml',
						success: function(data)
							{
								$(data).find('style').each(function()
									{
										var cellId = $(this).attr('id');
										var cellValue = $(this).text();

										$('#' + cellId).attr('class', cellValue);
										$('#' + cellId).attr('title', cellValue.replace('type', '') + '°');
									});
							},
						error: function() { alert('Arrghhh bug !'); }
					});

		});

        try
        {
		recognition = new webkitSpeechRecognition();
		recognition.continuous = false;
		recognition.interimResults = false;
		recognition.lang = '<?= SPEAK_LANGUAGE ?>';

		recognition.onresult = function (event)
			{
				for (var i = event.resultIndex; i < event.results.length; ++i)
					if (event.results[i].isFinal)
					{
						$('#speechText').val(event.results[i][0].transcript);
						$('#orderButton').click();
						break;
					}
                        };
		recognition.onend = function()
			{
				$('#speechButton').removeClass('speech-mic-works').addClass('speech-mic')
					.removeClass('glyphicon-remove-circle').addClass('glyphicon-ok-circle');
			};

		$('.speech-mic').click(function()
			{
				$('#speechButton').removeClass('speech-mic').addClass('speech-mic-works')
					.removeClass('glyphicon-ok-circle').addClass('glyphicon-remove-circle');
				recognition.start();
			});
		$('.speech-mic-works').click(function() { recognition.stop(); });

		$('#speechButton').attr('class', 'glyphicon glyphicon-ok-circle speech-mic');
        }
        catch(e)
        {
        }

	$('#orderButton').click(function()
		{
			var orderCmd = $('#speechText').val().trim();

			if (orderCmd != null && orderCmd.length > 0)
                                $.ajax({
                                                url: 'order.php',
                                                type: 'GET',
                                                data: 'msg=' + orderCmd,
                                                dataType: 'xml',
                                                success: function(data)
                                                        {
                                                                $(data).find('tell').each(function()
                                                                        {
										try
										{
											var msg = new SpeechSynthesisUtterance();

											msg.voiceURI = 'native';
											msg.volume = 1; // 0 to 1
											msg.rate = 1.5; // 0.1 (slow) to 10 (fast)
											msg.pitch = 1; //0 to 2
											msg.text = $(this).text();
											msg.lang = '<?= SPEAK_LANGUAGE ?>';
											window.speechSynthesis.speak(msg);
										}
										catch (e)
										{
											alert($(this).text());
										}
                                                                        });
                                                        },
                                                error: function() { alert('Arrghhh bug !'); }
                                        });

		});

});
 </script>
</body>
</html>
