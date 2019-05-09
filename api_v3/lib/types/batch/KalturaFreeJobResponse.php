<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunFreeJobResponse extends VidiunObject
{
	/**
	 * @var VidiunBatchJob
	 * @readonly 
	 */
	public $job;

	/**
	 * @var VidiunBatchJobType
	 * @readonly 
	 */
    public $jobType;
    
	/**
	 * @var int
	 * @readonly 
	 */
    public $queueSize;
}

?>