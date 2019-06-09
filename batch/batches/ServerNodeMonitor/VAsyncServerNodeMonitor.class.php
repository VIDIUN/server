<?php
/**
 * @package Scheduler
 * @subpackage ServerNodeMonitor
 */

/**
 * Will monitor server nodes and mark them NOT_REGISTERED if applicable
 *
 * @package Scheduler
 * @subpackage ServerNodeMonitor
 */
class VAsyncServerNodeMonitor extends VPeriodicWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::SERVER_NODE_MONITOR;
	}
	
	/* (non-PHPdoc)
	 * @see VBatchBase::run()
	*/
	public function run($jobs = null)
	{
		$filter = new VidiunServerNodeFilter();
		$filter->typeIn=self::$taskConfig->params->typesToMonitor;
		$filter->statusIn=VidiunServerNodeStatus::ACTIVE;
		$filter->heartbeatTimeLessThanOrEqual = (time() - self::$taskConfig->params->serverNodeTTL);
		$pager = new VidiunFilterPager();
		$pager->pageSize=500;
		$pager->pageIndex = 1;
		$serverNodes = self::$vClient->serverNode->listAction($filter, $pager);
		
		while ($serverNodes->objects && count($serverNodes->objects))
		{
			foreach ($serverNodes->objects as $serverNode)
			{
				/**
				 * @var VidiunEdgeServerNode $serverNode
				 */
				VidiunLog::info("ServerNode [" . $serverNode->id . "] is offline, last heartbeat [" . $serverNode->heartbeatTime . "]");
				try
				{
					self::$vClient->serverNode->markOffline($serverNode->id);
				}
				catch (Exception $e)
				{
					VidiunLog::info("Could not mark servernode offline, continuing [". $serverNode->id . "]");
				}
			}
			//No need to move the pager index since we change all the server-nodes we found from active to unregistered.
			$serverNodes = self::$vClient->serverNode->listAction($filter, $pager);
		}
	}
}
