<?php
/**
 * base class for the real ConversionEngines in the system - ffmpeg,menconder and flix. 
 * 
 * @package Scheduler
 * @subpackage Conversion.engines
 */
class VOperationEngineOldVersionWrapper extends VOperationEngine
{
	/**
	 * @var VConversionEngine
	 */
	protected $convertor;
	
	public function __construct($type, VidiunConvartableJobData $data)
	{
		$this->convertor = VConversionEngine::getInstance($type);
		$this->logFilePath = $data->destFileSyncLocalPath . ".log";
	}

	protected function doOperation()
	{
		list($ok, $errorMessage) = $this->convertor->convert($this->data);
		if(!$ok)
			throw new VOperationEngineException($errorMessage);
	}
	
	/**
	 * @param bool $enabled
	 */
	public function setMediaInfoEnabled($enabled)
	{
		$this->convertor->setMediaInfoEnabled($enabled);
	}
	
	/* (non-PHPdoc)
	 * @see VOperationEngine::getLogFilePath()
	 */
	public function getLogFilePath()
	{
		return $this->convertor->getLogFilePath();
	}
	
	/* (non-PHPdoc)
	 * @see VOperationEngine::getLogData()
	 */
	public function getLogData()
	{
		return $this->convertor->getLogData();
	}
	
	protected function getCmdLine(){}
}


