<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunWorkerQueueFilter extends VidiunObject
{
	/**
	 * @var int
	 */
	public $schedulerId;
	
    
	/**
	 * @var int
	 */
	public $workerId;
	
    
	/**
	 * @var VidiunBatchJobType
	 */
	public $jobType;
	
    
	/**
	 * @var VidiunBatchJobFilter
	 */
	public $filter;
	
    
}

