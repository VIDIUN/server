<?php
/**
 * @package plugins.tagSearch
 * @subpackage Scheduler
 */
class VAsyncTagIndex extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job) {
		
		$this->reIndexTags($job);
		
	}

	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::INDEX_TAGS;
	}
	
	protected function reIndexTags (VidiunBatchJob $job)
	{
		VidiunLog::info("Re-indexing tags according to privacy contexts");
		$tagPlugin = VidiunTagSearchClientPlugin::get(self::$vClient);
		$this->impersonate($job->partnerId);
		try 
		{
			$tagPlugin->tag->indexCategoryEntryTags($job->data->changedCategoryId, $job->data->deletedPrivacyContexts, $job->data->addedPrivacyContexts);
		}
		catch (Exception $e)
		{
			$this->unimpersonate();
			return $this->closeJob($job, VidiunBatchJobErrorTypes::VIDIUN_API, $e->getCode(), $e->getMessage(), VidiunBatchJobStatus::FAILED);
		}
		$this->unimpersonate();
		return $this->closeJob($job, null, null, "Re-index complete", VidiunBatchJobStatus::FINISHED);
		
	}
}