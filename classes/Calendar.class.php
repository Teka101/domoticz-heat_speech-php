<?php
class Calendar
{
	public function __construct()
	{
	}

	public function getDatesFromNow($counter)
	{
		$dt = new DateTime();
		$dt = $dt->setTime(0, 0, 0);
		$dates = array();
		for ($i = 0; $i < $counter; $i++)
		{
			$dt = $dt->add(new DateInterval('P1D'));
			$dates[] = $dt->format('U');
		}
		return $dates;
	}

	public function getDates($beginDate, $endDate)
	{
		$bDate = localtime($beginDate);
		$eDate = localtime($endDate);

		$bMonth = $bDate[4] + 1;
		$bYear = 1900 + $bDate[5];
		$eMonth = $eDate[4] + 1;
		$eYear = 1900 + $eDate[5];
		$nbMonths = ($eYear - $bYear) * 12 + ($eMonth - $bMonth);
		$dates = array();
		for ($posMonth = 0; $posMonth <= $nbMonths; $posMonth++)
		{
			$cMonth = $bMonth + $posMonth;
			$cYear = $bYear;
			while ($cMonth > 12)
			{
				$cMonth -= 12;
				$cYear++;
			}
			$cTime = mktime(1, 0, 0, $cMonth, 15, $cYear);
			$cText = ucfirst(strftime('%B %G', $cTime));
			$days = array();
			array_push($days, $cText);
			$nbDays = cal_days_in_month(CAL_GREGORIAN, $cMonth, $cYear);
			for ($posDay = 1; $posDay <= $nbDays; $posDay++)
			{
				$cTime = mktime(0, 0, 0, $cMonth, $posDay, $cYear);
				$cText = strftime('%d %a', $cTime);
				$days[$posDay] = $cText;
				$days["time-$posDay"] = $cTime;
			}
			array_push($dates, $days);
		}
		return $dates;
	}

	function isWeekend($time)
	{
		$date = localtime($time);
		return $date[6] == 0 || $date[6] == 6;
	}
}
?>
