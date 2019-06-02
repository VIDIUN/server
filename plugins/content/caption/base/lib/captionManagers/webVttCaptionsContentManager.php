<?php
/**
 * @package plugins.caption
 * @subpackage lib
 */
class webVttCaptionsContentManager extends vCaptionsContentManager
{

	const WEBVTT_TIMECODE_PATTERN = '#^((?:[0-9]{2}:)?[0-9]{2}:[0-9]{2}\.[0-9]{3}) --> ((?:[0-9]{2}:)?[0-9]{2}:[0-9]{2}\.[0-9]{3})( .*)?$#';

	const BOM_CODE =  "\xEF\xBB\xBF";
	const WEBVTT_PATTERN = 'WEBVTT';
	/**
	 * @var array
	 */
	protected $parsingErrors = array();

	/**
	 * @var array
	 */
	public $headerInfo = array();

	/* (non-PHPdoc)
	 * @see vCaptionsContentManager::parse()
	 */
	public function parse($content)
	{
		$itemsData =  $this->parseWebVTT($content);

		foreach ($itemsData as &$itemData )
		{
			foreach ($itemData['content'] as &$curChunk)
			{
				$val = strip_tags($curChunk['text']);
				$curChunk['text'] = $val;
			}
		}
		return $itemsData;
	}


	/**
	 * @param $parsing_errors
	 * @return array
	 */
	public function validateWebVttHeader($signature)
	{
		if (substr($signature, 0, 6) !== 'WEBVTT' && substr($signature, 0, 9) !== self::BOM_CODE.'WEBVTT')
		{
			$this->parsingErrors[] = 'Missing "WEBVTT" at the beginning of the file';
			return false;
		}

		if (strlen($signature) > 6 && substr($signature, 0, 6) === 'WEBVTT')
		{
			if (substr($signature, 0, 7) === 'WEBVTT ')
			{
				$fileDescription = substr($signature, 7);
				if (strpos($fileDescription, '-->') !== false)
				{
					$this->parsingErrors[] = 'File description must not contain "-->"';
					return false;
				}
				return true;
			}
			else
			{
				$this->parsingErrors[] = 'Invalid file header (must be "WEBVTT" with optional description)';
				return false;
			}
		} elseif (strlen($signature) > 9 && substr($signature, 0, 9) === self::BOM_CODE.'WEBVTT')
		{
			if (substr($signature, 0, 10) === self::BOM_CODE.'WEBVTT ')
			{
				$fileDescription = substr($signature, 10);
				if (strpos($fileDescription, '-->') !== false)
				{
					$this->parsingErrors[] = 'File description must not contain "-->"';
					return false;
				}
				return true;
			}
			else
			{
				$this->parsingErrors[] = 'Invalid file header (must be "WEBVTT" with optional description)';
				return false;
			}
		}
		return true;
	}


	/**
	 * @param $timeStr
	 * @return string
	 */
	public function parseWebvttStrTTTime($timeStr)
	{
		list ($timeInMilliseconds, $error) = vCaptionsContentManager::parseStrTTTime($timeStr);
		if($error)
			$this->parsingErrors[] = $error;
		return $timeInMilliseconds;
	}


	/* (non-PHPdoc)
	 * @see vCaptionsContentManager::getContent()
	 */
	public function getContent($content)
	{
		$itemsData = null;
		try
		{
			$itemsData = $this->parseWebVTT($content);

			$content = '';
			foreach ($itemsData as $itemData)
			{
				foreach ($itemData['content'] as $curChunk)
				{
					$text = strip_tags($curChunk['text']);
					$content .= $text. ' ';
				}
			}
		} catch (Exception $e)
		{
			VidiunLog::err($e->getMessage());
			return null;
		}
		return trim(preg_replace('/\s+/', ' ', $content));
	}

	/**
	 * @return webVttCaptionsContentManager
	 */
	public static function get()
	{
		return new webVttCaptionsContentManager();
	}


	/**
	 * @param $content
	 * @return array
	 */
	public function parseWebVTT($content)
	{
		$this->headerInfo = array();
		$foundFirstTimeCode = false;
		$itemsData = array();
		$fileContentArray = self::getFileContentAsArray($content);
		// Parse signature.
		$header = self::getNextValueFromArray($fileContentArray);
		if (!$this->validateWebVttHeader($header))
		{
			VidiunLog::err("Error Parsing WebVTT file. The following errors were found while parsing the file: \n" . print_r($this->parsingErrors, true));
			return array();
		}
		$this->headerInfo[] = $header.self::UNIX_LINE_ENDING;
		// Parse text - ignore comments, ids, styles, notes, etc
		while (($line = self::getNextValueFromArray($fileContentArray)) !== false)
		{
			// Timecode.
			$matches = array();
			$timecode_match = preg_match(self::WEBVTT_TIMECODE_PATTERN, $line, $matches);
			if ($timecode_match)
			{
				$foundFirstTimeCode = true;
				$start = $this->parseCaptionTime($matches[1]);
				$stop = $this->parseCaptionTime($matches[2]);
				$text = '';
				while (trim($line = self::getNextValueFromArray($fileContentArray)) !== '')
				{
					$line = $this->handleTextLines($line);
					$text .= $line . self::UNIX_LINE_ENDING;
				}
				$itemsData[] = array('startTime' => $start, 'endTime' => $stop, 'content' => array(array('text' => $text)));
			}elseif ($foundFirstTimeCode == false)
				$this->headerInfo[] = $line . self::UNIX_LINE_ENDING;
		};
		if (count($this->parsingErrors) > 0)
		{
			VidiunLog::err("Error Parsing WebVTT file. The following errors were found while parsing the file: \n" . print_r($this->parsingErrors, true));
			return array();
		}
		return $itemsData;
	}

	public function buildFile($content, $clipStartTime, $clipEndTime, $globalOffset = 0)
	{
		$newFileContent = $this->createCaptionsFile($content, $clipStartTime, $clipEndTime, self::WEBVTT_TIMECODE_PATTERN, $globalOffset);
		return $newFileContent;
	}

	protected function createAdjustedTimeLine($matches,  $clipStartTime, $clipEndTime, $globalOffset)
	{
		$startCaption = $this->parseWebvttStrTTTime($matches[1]);
		$endCaption = $this->parseWebvttStrTTTime($matches[2]);
		if (!TimeOffsetUtils::onTimeRange($startCaption, $endCaption, $clipStartTime, $clipEndTime))
			return null;
		$adjustedStartTime = TimeOffsetUtils::getAdjustedStartTime($startCaption, $clipStartTime, $globalOffset);
		$adjustedEndTime = TimeOffsetUtils::getAdjustedEndTime($endCaption, $clipStartTime, $clipEndTime, $globalOffset);
		$settings = isset($matches[3]) ? trim($matches[3]) : '';
		$timeLine = vWebVTTGenerator::formatWebVTTTimeStamp($adjustedStartTime) . ' --> ' . vWebVTTGenerator::formatWebVTTTimeStamp($adjustedEndTime). $settings . vCaptionsContentManager::UNIX_LINE_ENDING;
		return $timeLine;
	}

	/**
	 * @param string $content
	 * @param string $toAppend
	 * @return string
	 */
	public function merge($content, $toAppend)
	{
		if (!$toAppend)
			return $content;

		$originalFileContentArray = vCaptionsContentManager::getFileContentAsArray($toAppend);
		while (($line = vCaptionsContentManager::getNextValueFromArray($originalFileContentArray)) !== false)
		{
			if (strpos($line,self::WEBVTT_PATTERN) === false)
				$content .= $line . vCaptionsContentManager::UNIX_LINE_ENDING;
		}
		return $content;
	}
}