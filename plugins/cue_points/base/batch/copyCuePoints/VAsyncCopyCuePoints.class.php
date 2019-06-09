<?php
/**
 * @package Scheduler
 * @subpackage copyCuePoints
 */

class VAsyncCopyCuePoints extends VJobHandlerWorker
{
	const MAX_CUE_POINTS_TO_COPY_TO_VOD = 500;

	/*
	 * (non-PHPdoc)
	 *  @see VBatchBase::getJobType();
	 */
	const ATTEMPT_ALLOWED = 3;

	public static function getType()
	{
		return VidiunBatchJobType::COPY_CUE_POINTS;
	}

	/*
	 * (non-PHPdoc)
	 *  @see VBatchBase::getJobType();
	 */
	public static function getJobType()
	{
		return VidiunBatchJobType::COPY_CUE_POINTS;
	}


	/**
	 * @param VidiunBatchJob $job
	 * @return VidiunBatchJob
	 */
	protected function exec(VidiunBatchJob $job)
	{
		$engine = VCopyCuePointEngine::initEngine($job->jobSubType, $job->data, $job->partnerId);
		if (!$engine)
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::ENGINE_NOT_FOUND,
							"Cannot find copy engine [{$job->jobSubType}]", VidiunBatchJobStatus::FAILED);
		if (!$engine->validateJobData())
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::MISSING_PARAMETERS,
				"Job subType [{$job->jobSubType}] has missing job data", VidiunBatchJobStatus::FAILED);
		if (!$engine->copyCuePoints())
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, null,
				"Job has failed in copy the cue points", VidiunBatchJobStatus::FAILED);

		return $this->closeJob($job, null, null, "All Cue Point Copied ", VidiunBatchJobStatus::FINISHED);
	}

}
