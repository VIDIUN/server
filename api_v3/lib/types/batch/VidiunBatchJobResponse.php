<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBatchJobResponse extends VidiunObject 
{
	/**
	 * The main batch job
	 * 
	 * @var VidiunBatchJob
	 */
	public $batchJob;
	
	
	/**
	 * All batch jobs that reference the main job as root
	 * 
	 * @var VidiunBatchJobArray
	 */
	public $childBatchJobs;
}