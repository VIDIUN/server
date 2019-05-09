<?php
/**
 * Interface which allows plugin to add its own configuration to a batch job.
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunBatchJobDataContributor extends IVidiunBase
{
	/**
	 * Contribute to convert job data 
	 * @param vConvertJobData $jobData
	 * @returns vConvertJobData
	 */ 
	public static function contributeToConvertJobData ($jobType, $jobSubType, vConvertJobData $jobData);
}