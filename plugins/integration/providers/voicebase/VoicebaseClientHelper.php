<?php
/**
 * @package plugins.voicebase
 */
class VoicebaseClientHelper
{
	const VOICEBASE_FAILURE_MESSAGE = "FAILURE";
	const VOICEBASE_MACHINE_COMPLETE_REQUEST_STATUS = "SUCCESS";
	const VOICEBASE_MACHINE_COMPLETE_MESSAGE = "MACHINECOMPLETE";
	const VOICEBASE_HUMAN_COMPLETE_MESSAGE = "HUMANCOMPLETE";
	const VOICEBASE_MACHINE_FAILURE_MESSAGE = "ERROR";
	
	const VOICEBASE_ACTION_UPLOADMEDIA = "uploadMedia";
	const VOICEBASE_ACTION_GETFILESTATUS = "getFileStatus";
	const VOICEBASE_ACTION_UPDATETRANSCRIPT = "updateTranscript";
	const VOICEBASE_ACTION_GETTRANSCRIPT = "getTranscript";
	const VOICEBASE_ACTION_DELETEFILE = "deleteFile";

	private $supportedLanguages = array();
	private $baseEndpointUrl = null;

    /**
     * @var array
     * Property contains additional parameters to be dispatched to VoiceBase, grouped by action name.
     */
	private $additionalParams = array();
	
	public function __construct($apiKey, $apiPassword, $additionalParams = null)
	{
		$voicebaseParamsMap = vConf::get('voicebase','integration');
		$this->supportedLanguages = $voicebaseParamsMap['languages'];
		$version = $voicebaseParamsMap['version'];
	
		$url = $voicebaseParamsMap['base_url'];
		$params = array("version" => $version, "apikey" => $apiKey, "password" => $apiPassword);
		
		$url = $this->addUrlParams($url, $params, true);
		$this->baseEndpointUrl = $url;

		if ($additionalParams)
		{
			$this->additionalParams = $additionalParams;
		}
	}
	
	public function checkExistingExternalContent($externalId)
	{	
		$curlResult = $this->retrieveRemoteProcess($externalId);
		if($curlResult)
		{
			if ($curlResult->requestStatus == self::VOICEBASE_FAILURE_MESSAGE || !isset($curlResult->fileStatus) || !in_array($curlResult->fileStatus, array(self::VOICEBASE_MACHINE_COMPLETE_MESSAGE, self::VOICEBASE_HUMAN_COMPLETE_MESSAGE)))
				return false;
			return true;
		}
		
		return false;
	}
	
	public function retrieveRemoteProcess ($externalId)
	{
		$params = array("action" => self::VOICEBASE_ACTION_GETFILESTATUS, "externalID" => $externalId);
		$curlResult = $this->sendAPICall($params);
		
		return $curlResult;
	}
	
	public function uploadMedia($flavorUrl, $entryId, $externalId, $callBackUrl, $spokenLanguage, $fileLocation = null)
	{
		if($spokenLanguage)
			$spokenLanguage = $this->supportedLanguages[$spokenLanguage];
		
		$params = array("action" => self::VOICEBASE_ACTION_UPLOADMEDIA,
						"title" => $entryId,
						"externalID" => $externalId,
						"lang" => $spokenLanguage
						);

		if (isset($this->additionalParams[self::VOICEBASE_ACTION_UPLOADMEDIA]))
		{
			$params = array_merge($params, $this->additionalParams[self::VOICEBASE_ACTION_UPLOADMEDIA]);
		}

		$postParams = array("mediaURL" => $flavorUrl);
		if($fileLocation)
		{
			$adjustedLocation = $this->getFile($fileLocation);
			$postParams["transcript"] = $adjustedLocation;
			$postParams["transcriptType"] = "human";
			$postParams["humanReadyCallBack"] = $callBackUrl;
		}
		else
		{
			$postParams["transcriptType"] = "machine-bestAvailable";
			$postParams["machineReadyCallBack"] = $callBackUrl;
		}
		$urlOptions = array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $postParams);

		$curlResult = $this->sendAPICall($params, $urlOptions);
		if ($curlResult->requestStatus == VoicebaseClientHelper::VOICEBASE_FAILURE_MESSAGE)
		{
			$action = $params["action"];
			throw new Exception("VoiceBase $action failed. Message: [" . $curlResult->statusMessage . "]");
		}
		
		return true;
	}
	
	private function sendAPICall($params, $options = null, $noDecoding = false)
	{
		$url = $this->addUrlParams($this->baseEndpointUrl, $params);
		VidiunLog::debug("sending API call - $url");

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
		if ($options)
			curl_setopt_array($ch, $options);

		$result = curl_exec($ch);

		if(($errString = curl_error($ch)) !== '' || ($errNum = curl_errno($ch)) !== 0)
		{
			VidiunLog::err('problem with curl - ' . $errString . ' error num - ' . $errNum);
			curl_close($ch);
			throw new Exception("curl error with url " . $url . " error num [$errNum] error message [$errString]");
		}
		if(!$noDecoding)
		{
			$stringResult = $result;
			$result = json_decode($result);
				
			if (json_last_error() !== JSON_ERROR_NONE)
			{
				curl_close($ch);
				throw new Exception("json decode error with response - " . $stringResult);
			}
			
		}

		VidiunLog::info('result is - ' . var_dump($result));
		curl_close($ch);
		
		return $result;
	}
	
	public function updateRemoteTranscript($externalId, $transcriptContent, $callBack)
	{
		$params = array("action" => self::VOICEBASE_ACTION_UPDATETRANSCRIPT, "externalID" => $externalId);

		if (isset($this->additionalParams[self::VOICEBASE_ACTION_UPDATETRANSCRIPT]))
		{
			$params = array_merge($params, $this->additionalParams[self::VOICEBASE_ACTION_UPDATETRANSCRIPT]);
		}

		$transcriptContent = $this->getFile($transcriptContent);
		$postFields = array(
				"transcript" => $transcriptContent,
				"machineReadyCallBack" => $callBack,
				"humanReadyCallBack" => $callBack,
		);
		$options = array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $postFields);
	
		$result = $this->sendAPICall($params, $options);
		if ($result->requestStatus == VoicebaseClientHelper::VOICEBASE_FAILURE_MESSAGE)
		{
			$action = $params["action"];
			throw new Exception("VoiceBase $action failed. Message: [" . $result->statusMessage . "]");
		}

		return $result;
	}
	
	public function getRemoteTranscripts($externalId, array $formats)
	{	
		$params = array("action" => self::VOICEBASE_ACTION_GETTRANSCRIPT, "externalID" => $externalId);
	
		$results = array();
		foreach($formats as $format)
		{
			$params["format"] = $format;
			$result = $this->sendAPICall($params);
			//fixing a service-provider API v1 bug
			if($format == "TXT")
			{
				//removing each pattern of zero/one space followed by \n\n , where it comes after a char not in {.?!}
				$patterns = array("/([^\.\?!\s])(\n\n)/", "/([^\.\?!])(\s\n\n)/");
				$replacements = array("$1", "$1");
				$result->transcript = preg_replace($patterns, $replacements, $result->transcript);
			}
			$results[$format] = $result->transcript;
		}
		
		return $results;
	}
	
	public function calculateAccuracy($externalId)
	{
		$contentArr = $this->getRemoteTranscripts($externalId, array("JSON"));
		$transcriptWordObjects = json_decode($contentArr["JSON"]);
		$sumOfAccuracies = 0;
		$numberOfElements = 0;
		
		foreach($transcriptWordObjects as $wordObject)
		{
			if(isset($wordObject->c) && 0 <= $wordObject->c && $wordObject->c <= 1)
			{
				$sumOfAccuracies += $wordObject->c;
				$numberOfElements++;
			}
		}
	
		if($numberOfElements)
			return $sumOfAccuracies/$numberOfElements;
		
		return 0;
	}
	
	public function deleteRemoteFile($externalId)
	{	
		$params = array("action" => self::VOICEBASE_ACTION_DELETEFILE, "externalID" => $externalId);
	
		$curlResult = $this->sendAPICall($params);
	}

	private function getFile($path)
	{
		if (PHP_VERSION_ID >= 50500)
			return new \CURLFile($path);
		else
			return '@' . $path;
	}

	private function addUrlParams($url, array $params, $init = false)
	{
		$url .= $init ? '?' : '&' ;
		
		return $url . http_build_query($params);
	}
}
