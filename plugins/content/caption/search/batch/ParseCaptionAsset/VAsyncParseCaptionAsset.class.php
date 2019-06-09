<?php
/**
 * @package plugins.captionSearch
 * @subpackage Scheduler
 */
class VAsyncParseCaptionAsset extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::PARSE_CAPTION_ASSET;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->parse($job, $job->data);
	}
	
	protected function parse(VidiunBatchJob $job, VidiunParseCaptionAssetJobData $data)
	{
		try
		{
			$this->updateJob($job, "Start parsing caption asset [$data->captionAssetId]", VidiunBatchJobStatus::QUEUED);
			
			$captionSearchPlugin = VidiunCaptionSearchClientPlugin::get(self::$vClient);
			$captionSearchPlugin->captionAssetItem->parse($data->captionAssetId);
			
			$this->closeJob($job, null, null, "Finished parsing", VidiunBatchJobStatus::FINISHED);
		}
		catch(Exception $ex)
		{
			$this->closeJob($job, VidiunBatchJobErrorTypes::RUNTIME, $ex->getCode(), "Error: " . $ex->getMessage(), VidiunBatchJobStatus::FAILED, $data);
		}
		return $job;
	}
}
