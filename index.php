<?php
	require 'config.php';
	require 'classes/Calendar.class.php';
	require 'classes/Heating.class.php';

	$calYear = date('Y');
	if (array_key_exists('year', $_GET))
		$calYear = $_GET['year'];
	$calendar = new Calendar($calYear);
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
		if (array_key_exists('addDays', $_GET))
		{
			$counter = $_GET['addDays'];
			$temp = $heating->getBestTempatures($counter);
			$dates = $calendar->getDatesFromNow($counter);
			foreach ($dates as $date)
				$heating->setDateTemperature($date, $temp);
			echo '<done/>';
		}
		echo '</responses>';
		exit(0);
	}
	else if (array_key_exists('whatNow', $_GET))
	{
		$temperature = $heating->getCurrentTemperature();
		echo $temperature;
		exit(0);
	}

	header('Content-Type: text/html; charset=UTF-8');
	$heating = new Heating();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Domoticz control</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="web/bootstrap-3.2.0-dist/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="web/bootstrap-3.2.0-dist/css/bootstrap-theme.min.css">
	<link rel="stylesheet" type="text/css" href="web/style.css">
	<script type="text/javascript" src="web/jquery-2.1.1.min.js"></script>
</head>
<body>
	<div class="center-block text-center">
	<form class="form-inline" role="form">
		<div class="input-group">
			<div class="input-group-addon">Speak</div>
			<input type="text" placeholder="Speak and order !">
		</div>
		<button type="submit" class="btn btn-default">Execute</button>
	</form>
	</div>
	<p>
	Assign temperature: 
	<select id="typeDay">
<?php
	$types = $heating->getTypes();
	foreach ($types as $type)
	{
		echo '<option>';
		echo $type;
		echo '</option>';
	}
?>
	</select>
	</p>
	<div class="row">
		<div class="col-xs-6 text-left"><a class="btn btn-default" href="<?= $_SERVER['PHP_SELF'] ?>?year=<?= ($calYear - 1) ?>">&lt;&lt;</a></div>
		<div class="col-xs-6 text-right"><a class="btn btn-default" href="<?= $_SERVER['PHP_SELF'] ?>?year=<?= ($calYear + 1) ?>">&gt;&gt;</a></div>
	</div>
	<div align="center"><table class="table table-bordered table-condensed text-center" style="width: auto">
<?php

	$begin = mktime(0, 1, 0, 1, 1, $calYear);
	$end = mktime(0, 1, 0, 1, 1, $calYear + 1);
	$nbMonths = 13;
	$ret = $calendar->getDates($begin, $end);
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
				echo ' id="d'.$cDate.'" class="';
				if (($typeDate = $heating->getDateType($cDate)))
					echo 'type' . $typeDate;
				else if ($calendar->isWeekend($cDate))
					echo 'weekend';
				else
					echo 'type' . $heating->getDefaultType();
				echo '"';
				echo '>' . $cText;
			}
			else
				echo '>&nbsp;';
			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody>';
?>
	</table></div>
	<script>
$(function()
{
	$('.calendar table td').click(function()
		{
			var calDate = $(this).attr('id').substring(1);
			var typeClass = $(this).attr('class');
			var newTypeClass = $('#typeDay option:selected').text();

			typeClass = typeClass.replace('type', '');
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
									});
							},
						error: function() { alert('Arrghhh bug !'); }
					});

		});
});
	</script>
</body>
</html>
