<?php

class SpeechRecognize
{
	private $allSentences;
	private $allWords;

	public function	__construct($sentences, $words)
	{
		$this->allSentences = $sentences;
		$this->allWords = $words;
	}

	public function parseAndExecute($sentence)
	{
		$sentence = $this->normalizeText($sentence);
		$words = explode(' ', $sentence);
		$words = $this->parse($words);
		//print_r($words);
		if ($words && count($words) > 0)
		{
			$sentence = implode(' ', $words);
			foreach ($this->allSentences as $s)
			{
				if (preg_match('/' . $s['text'] . '/', $sentence, $matches) > 0)
				{
					call_user_func($s['action'], $matches);
					return true;
				}
			}
		}
		return false;
	}

	private function parse($words)
	{
		$r = array();
		foreach ($words as $word)
		{
			$type = $this->parseWord($word);
			if ($type)
				$r[] = $type;
		}
		return $r;
	}

	private function parseWord($word)
	{
		if (is_numeric($word))
			return $word;
		foreach ($this->allWords as $type => &$wordsValues)
		{
			if (is_array($wordsValues))
			{
				if (in_array($word, $wordsValues))
					return $type;
			}
			else
			{
				foreach ($wordsValues as $value => &$list)
					if (in_array($word, $list))
						return $value;
			}
		}
		return null;
	}

	private function normalizeText($sentence)
	{
		$sentence = iconv('UTF-8', 'ASCII//TRANSLIT', $sentence);
		$sentence = str_replace("'", ' ', strtolower($sentence));
		$sentence = str_replace(array('?', '.', ','), '', $sentence);
		return $sentence;
	}
}
?>
