<?php
	$SR_WORDS = array();
	$SR_WORDS['EAGGER_1'] = array('coucou');
	$SR_WORDS['TO_LEAVE'] = array('partir', 'pars', 'reviens');
	$SR_WORDS['DURATION_DAY'] = array('jour', 'jours', 'journee');
	$SR_WORDS['DURATION_WEEK'] = array('semaine', 'semaines');
	$SR_WORDS['DURATION_MONTH'] = array('mois');
	$SR_WORDS['NUMBERS'] = json_decode('{ "1": ["un", "une"], "2": ["deux"], "3": ["trois"], "4": ["quatre"], "5": ["cinq"], "6": ["six"], "7": ["sept"], "8": ["huit"], "9": ["neuf"] }');

	$SR_SENTENCES = array();
	$SR_SENTENCES[] = array('text' => '^EAGGER_1$', 'action' => 'cbEagger');
	$SR_SENTENCES[] = array('text' => '^TO_LEAVE ([0-9]+) DURATION_DAY$', 'action' => 'cbLeaveHouseDay');
	$SR_SENTENCES[] = array('text' => '^TO_LEAVE ([0-9]+) DURATION_WEEK$', 'action' => 'cbLeaveHouseWeek');
	$SR_SENTENCES[] = array('text' => '^TO_LEAVE ([0-9]+) DURATION_MONTH$', 'action' => 'cbLeaveHouseMonth');
?>
