<?php
/**
 * @package Scheduler
 * @subpackage TagResolver
 */
class VAsyncTagResolve extends VPeriodicWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::run()
	 */
	public function run($jobs = null) 
	{
		$tagPlugin = VidiunTagSearchClientPlugin::get(self::$vClient);
		$deletedTags = $tagPlugin->tag->deletePending();
		
		VidiunLog::info("Finished resolving tags: $deletedTags tags removed from DB");
	}
	
	/**
	 * @return int
	 * @throws Exception
	 */
	public static function getType()
	{
		return VidiunBatchJobType::TAG_RESOLVE;
	}

	
}