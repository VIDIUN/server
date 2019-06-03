<?php
/**
 * @package Scheduler
 * @subpackage ValidateLiveMediaServers
 */

/**
 * Validates periodically that all live entries are still broadcasting to the connected media servers
 *
 * @package Scheduler
 * @subpackage ValidateLiveMediaServers
 */
class VAsyncValidateLiveMediaServers extends VPeriodicWorker
{
	const ENTRY_SERVER_NODE_MIN_CREATION_TIMEE = 120;
	
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::CLEANUP;
	}
	
	/* (non-PHPdoc)
	 * @see VBatchBase::run()
	*/
	public function run($jobs = null)
	{
		$entryServerNodeMinCreationTime = $this->getAdditionalParams("minCreationTime");
		if(!$entryServerNodeMinCreationTime)
			$entryServerNodeMinCreationTime = self::ENTRY_SERVER_NODE_MIN_CREATION_TIMEE;
		
		$entryServerNodeFilter = new VidiunEntryServerNodeFilter();
		$entryServerNodeFilter->orderBy = VidiunEntryServerNodeOrderBy::CREATED_AT_ASC;
		$entryServerNodeFilter->createdAtLessThanOrEqual = time() - $entryServerNodeMinCreationTime;
		
		$entryServerNodePager = new VidiunFilterPager();
		$entryServerNodePager->pageSize = 500;
		$entryServerNodePager->pageIndex = 1;
		
		$entryServerNodes = self::$vClient->entryServerNode->listAction($entryServerNodeFilter, $entryServerNodePager);
		
		while($entryServerNodes->objects && count($entryServerNodes->objects))
		{
			foreach($entryServerNodes->objects as $entryServerNode)
			{
				try
				{
					/* @var $entryServerNode VidiunEntryServerNode */
					self::impersonate($entryServerNode->partnerId);
					self::$vClient->entryServerNode->validateRegisteredEntryServerNode($entryServerNode->id);
					self::unimpersonate();
				}
				catch (VidiunException $e)
				{
					self::unimpersonate();
					VidiunLog::err("Caught exception with message [" . $e->getMessage()."]");
				}
			}
			
			$entryServerNodePager->pageIndex++;
			$entryServerNodes = self::$vClient->entryServerNode->listAction($entryServerNodeFilter, $entryServerNodePager);
		}
	}
}
