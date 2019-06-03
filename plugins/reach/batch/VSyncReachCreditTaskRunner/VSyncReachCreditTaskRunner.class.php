<?php
/**
 * @package plugins.reach
 * @subpackage Scheduler
 */
class VSyncReachCreditTaskRunner extends VPeriodicWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::SYNC_REACH_CREDIT_TASK;
	}

	/* (non-PHPdoc)
	 * @see VBatchBase::getJobType()
	 */
	public function getJobType()
	{
		return self::getType();
	}

	/* (non-PHPdoc)
	 * @see VBatchBase::run()
	*/
	public function run($jobs = null)
	{
		$reachClient = $this->SyncReachClient();
		$filter = new VidiunReachProfileFilter();
		$filter->statusEqual = VidiunReachProfileStatus::ACTIVE;
		$pager = new VidiunFilterPager();
		$pager->pageIndex = 1;
		$pager->pageSize = 500;
		
		
		do {
			$result = $reachClient->reachProfile->listAction($filter, $pager);
			foreach ($result->objects as $reachProfile)
			{
				try
				{
					$this->syncReachProfileCredit($reachProfile);
				}
				catch (Exception $ex)
				{
					VidiunLog::err($ex);
				}
			}
			
			$pager->pageIndex++;
		}  while(count($result->objects) == $pager->pageSize);
	}

	/**
	 * @param VidiunReachProfile $reachProfile
	 */
	protected function syncReachProfileCredit(VidiunReachProfile $reachProfile)
	{
		$reachClient = $this->SyncReachClient();
		$this->impersonate($reachProfile->partnerId);
		try
		{
			$result = $reachClient->reachProfile->syncCredit($reachProfile->id);
			$this->unimpersonate();
		}
		catch (Exception $ex)
		{
			$this->unimpersonate();
			throw $ex;
		}
	}

	/**
	 * @return VidiunReachClientPlugin
	 */
	protected function SyncReachClient()
	{
		$client = $this->getClient();
		return VidiunReachClientPlugin::get($client);
	}
}
