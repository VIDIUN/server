<?php
/**
 * base class for the real VBulkUploadEngine in the system 
 * 
 * @package Scheduler
 * @subpackage BulkUpload
 * @abstract
 */
abstract class VBulkUploadEngine
{
	public static $actionsMap = array(
		VidiunBulkUploadAction::ADD => 'add',
		VidiunBulkUploadAction::UPDATE => 'update',
		VidiunBulkUploadAction::DELETE => 'delete',
		VidiunBulkUploadAction::REPLACE => 'replace',
		VidiunBulkUploadAction::TRANSFORM_XSLT => 'transformxslt'
	);
	
	const BULK_UPLOAD_DATE_FORMAT = '%Y-%m-%d';
	const BULK_UPLOAD_TIME_FORMAT = 'T%H:%i:%s';

	
	/**
	 * 
	 * The batch current partner id
	 * @var int
	 */
	protected $currentPartnerId;
	
		
	/**
	 * @var int
	 */
	protected $multiRequestSize = 5;
	
	/**
	 * @var int
	 */
	protected $maxRecords = false;
	
	/**
	 * @var int
	 */
	protected $maxRecordsEachRun = 100;
	
	/**
	 * @var int
	 */
	protected $handledRecordsThisRun = 0;
	
	/**
	 * @var bool
	 */
	protected $exceededMaxRecordsEachRun = false;

	
	/**
	 * 
	 * @var VidiunBatchJob
	 */
	protected $job = null;
	
	/**
	 * 
	 * @var VidiunBulkUploadJobData
	 */
	protected $data = null;

	/**
	 * @param string $class enum class name
	 * @param string $value
	 * @return bool
	 */
	protected function isValidEnumValue($class, $value)
	{
		if(!class_exists($class))
			return false;
			
		$reflect = new ReflectionClass($class);
		$constants = $reflect->getConstants();
		foreach ($constants as $constant => $val)
		{
		    $constants[$constant] = strval($val);
		}
		
		if(!in_array($value, $constants))
		{
			VidiunLog::info("Value [$value] not found in class [$class] constants [" . print_r($constants, true) . "]");
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param string $str
	 * @return int
	 */
	public static function parseFormatedDate($str, $dateOnly = false)
	{
//		if(function_exists('strptime'))
//		{
//			$ret = strptime($str, self::BULK_UPLOAD_DATE_FORMAT . ($dateOnly ? '' : self::BULK_UPLOAD_TIME_FORMAT));
//			if($ret)
//			{
//			    $date = gmmktime($ret["tm_hour"], $ret["tm_min"], $ret["tm_sec"], $ret["tm_mon"], $ret["tm_mday"], $ret["tm_year"]);
//			    VidiunLog::debug("Formated Date [$date] " . date('Y-m-d\TH:i:s', $date));
//				return $date;
//			}
//		}
			
		$fields = null;
		$regex = self::getDateFormatRegex($fields, $dateOnly);
		
		$values = null;
		if(!preg_match($regex, $str, $values))
			return null;
			
		$hour = 0;
		$minute = 0;
		$second = 0;
		$month = 0;
		$day = 0;
		$year = 0;
		$is_dst = 0;
		
		foreach($fields as $index => $field)
		{
			$value = $values[$index + 1];
			
			switch($field)
			{
				case 'Y':
					$year = intval($value);
					break;
					
				case 'm':
					$month = intval($value);
					break;
					
				case 'd':
					$day = intval($value);
					break;
					
				case 'H':
					$hour = intval($value);
					break;
					
				case 'i':
					$minute = intval($value);
					break;
					
				case 's':
					$second = intval($value);
					break;
					
//				case 'T':
//					$date = date_parse($value);
//					$hour -= ($date['zone'] / 60);
//					break;
					
			}
		}
		
		VidiunLog::debug("gmmktime($hour, $minute, $second, $month, $day, $year)");
		$ret = gmmktime($hour, $minute, $second, $month, $day, $year);
		if($ret)
		{
			VidiunLog::debug("Formated Date [$ret] " . gmdate('Y-m-d\TH:i:s', $ret));
			return $ret;
		}
		return null;
	}
		
	/**
	 * @param string $str
	 * @return boolean
	 */
	protected function isUrl($str)
	{
		$str = VCurlWrapper::encodeUrl($str);

		$redundant_url_chars = array("_");

		$str = str_replace($redundant_url_chars , "" , $str);

		return filter_var($str, FILTER_VALIDATE_URL);
	}
		
	/**
	 * @param array $fields
	 * @return string
	 */
	private static function getDateFormatRegex(&$fields = null, $dateOnly = false)
	{
		$replace = array(
			'%Y' => '([1-2][0-9]{3})',
			'%m' => '([0-1][0-9])',
			'%d' => '([0-3][0-9])',
			'%H' => '([0-2][0-9])',
			'%i' => '([0-5][0-9])',
			'%s' => '([0-5][0-9])',
//			'%T' => '([A-Z]{3})',
		);
	
		$format = self::BULK_UPLOAD_DATE_FORMAT . ($dateOnly ? '' : self::BULK_UPLOAD_TIME_FORMAT);
		
		$fields = array();
		$arr = null;
		if(!preg_match_all('/%([YmdTHis])/', $format, $arr))
			return false;
	
		$fields = $arr[1];
		
		return '/' . str_replace(array_keys($replace), $replace, $format) . '/';
	}
	
	/**
	 * @param string $str
	 * @param bool $dateOnly
	 * @return bool
	 */
	public static function isFormatedDate($str, $dateOnly = false)
	{
	    $fields = null;
		$regex = self::getDateFormatRegex($fields, $dateOnly);
		return preg_match($regex, $str);
	}
	

	/**
	 * @param VidiunBatchJob $job
	 */
	public function __construct(VidiunBatchJob $job)
	{
		if(VBatchBase::$taskConfig->params->multiRequestSize)
			$this->multiRequestSize = VBatchBase::$taskConfig->params->multiRequestSize;
		if(VBatchBase::$taskConfig->params->maxRecords)
			$this->maxRecords = VBatchBase::$taskConfig->params->maxRecords;
		if(VBatchBase::$taskConfig->params->maxRecordsEachRun)
			$this->maxRecordsEachRun = VBatchBase::$taskConfig->params->maxRecordsEachRun;
		
		$this->job = $job;
		$this->data = $job->data;
		
		$this->currentPartnerId = $this->job->partnerId;
	}
	
	/**
	 * Will return the proper engine depending on the type (VidiunBulkUploadType)
	 *
	 * @param int $provider
	 * @return VBulkUploadEngine
	 */
	public static function getEngine($batchJobSubType, VidiunBatchJob $job)
	{
		//Gets the engine from the plugin (as we moved all engines to the plugin)
		return VidiunPluginManager::loadObject('VBulkUploadEngine', $batchJobSubType, array($job));
	}
	
	/**
	 * @return string
	 */
	public function getName()
	{
		return get_class($this);
	}
	
	
	/**
	 * @return VidiunBatchJob
	 */
	public function getJob()
	{
		return $this->job;
	}

	/**
	 * @return VidiunBulkUploadJobData
	 */
	public function getData()
	{
		return $this->data;
	}


	/**
	 * @param VidiunBatchJob $job
	 */
	public function setJob(VidiunBatchJob $job)
	{
		$this->job = $job;
	}

	/**
	 * @param VidiunBulkUploadJobData $data
	 */
	public function setData(VidiunBulkUploadJobData $data)
	{
		$this->data = $data;
	}
	
	/**
	 * @return bool
	 */
	public function shouldRetry()
	{
		return $this->exceededMaxRecordsEachRun;
	}

		
	/**
	 * 
	 * Handles the bulk upload
	 */
	abstract public function handleBulkUpload();
			
	/**
	 * 
	 * Adds a bulk upload result
	 * @param VidiunBulkUploadResult $bulkUploadResult
	 */
	protected function addBulkUploadResult(VidiunBulkUploadResult $bulkUploadResult)
	{
		$pluginsData = $bulkUploadResult->pluginsData;
		$bulkUploadResult->pluginsData = null;
		VBatchBase::$vClient->batch->addBulkUploadResult($bulkUploadResult, $pluginsData);
	}

	/**
	 * 
	 * Gets the start line number for the given job id
	 * @return int - the start line for the job id
	 */
	protected function getStartIndex()
	{
		try{
			$result = VBatchBase::$vClient->batch->getBulkUploadLastResult($this->job->id);
			if($result)
				return $result->lineIndex;
		}
		catch(Exception $e){
			VidiunLog::notice("getBulkUploadLastResult: " . $e->getMessage());
		}
		return 0;
	}
	
	/**
	 * save the results for returned created entries
	 * 
	 * @param array $requestResults
	 * @param array $bulkUploadResults
	 */
	protected function updateObjectsResults(array $requestResults, array $bulkUploadResults)
	{
		
	}
	
	/**
	 * 
	 * Checks if the job was aborted (throws exception if so)
	 * @throws VidiunBulkUploadAbortedException
	 */
	protected function checkAborted()
	{
		if(VBatchBase::$vClient->isMultiRequest())
			return false;
			
		$batchJobResponse = VBatchBase::$vClient->jobs->getBulkUploadStatus($this->job->id);
		$updatedJob = $batchJobResponse->batchJob;
		if($updatedJob->abort)
		{
			VidiunLog::info("job[{$this->job->id}] aborted");
				
			//Throw exception and close the job from the outside 
			throw new VidiunBulkUploadAbortedException("Job was aborted", VidiunBulkUploadAbortedException::JOB_ABORTED);
		}
		return false;
	}
	
	/**
	 * 
	 * Get object type title for messaging purposes
	 */
	abstract public function getObjectTypeTitle();
	
	public function getCurrentPartnerId()
	{
		return $this->currentPartnerId;
	}
}