<?php
/**
 * base class for the real ProvisionEngine in the system - currently only akamai 
 * 
 * @package Scheduler
 * @subpackage Provision
 * @abstract
 */
abstract class VProvisionEngine
{
	
	/**
	 * Will return the proper engine depending on the type (VidiunSourceType)
	 *
	 * @param int $provider
	 * @param VidiunProvisionJobData $data
	 * @return VProvisionEngine
	 */
	public static function getInstance ( $provider , VidiunProvisionJobData $data = null)
	{
		$engine =  null;
		
		switch ($provider )
		{
			case VidiunSourceType::AKAMAI_LIVE:
				$engine = new VProvisionEngineAkamai($data);
				break;
			case VidiunSourceType::AKAMAI_UNIVERSAL_LIVE:
				$engine = new VProvisionEngineUniversalAkamai($data);
				break;
			default:
				$engine = VidiunPluginManager::loadObject('VProvisionEngine', $provider);
		}
		
		return $engine;
	}

	
	/**
	 * @return string
	 */
	abstract public function getName();
	
	/**
	 * @param VidiunBatchJob $job
	 * @param VidiunProvisionJobData $data
	 * @return VProvisionEngineResult
	 */
	abstract public function provide( VidiunBatchJob $job, VidiunProvisionJobData $data );
	
	/**
	 * @param VidiunBatchJob $job
	 * @param VidiunProvisionJobData $data
	 * @return VProvisionEngineResult
	 */
	abstract public function delete( VidiunBatchJob $job, VidiunProvisionJobData $data );
	
	/**
	 * @param VidiunBatchJob $job
	 * @param VidiunProvisionJobData $data
	 * @return VProvisionEngineResult
	 */
	abstract public function checkProvisionedStream ( VidiunBatchJob $job, VidiunProvisionJobData $data ) ;
}


/**
 * @package Scheduler
 * @subpackage Conversion
 *
 */
class VProvisionEngineResult
{
	/**
	 * @var int
	 */
	public $status;
	
	/**
	 * @var string
	 */
	public $errMessage;
	
	/**
	 * @var VidiunProvisionJobData
	 */
	public $data;
	
	/**
	 * @param int $status
	 * @param string $errMessage
	 * @param VidiunProvisionJobData $data
	 */
	public function __construct( $status , $errMessage, VidiunProvisionJobData $data = null )
	{
		$this->status = $status;
		$this->errMessage = $errMessage;
		$this->data = $data;
	}
}

