<?php
/**
 * @package plugins.scheduledTask
 * @subpackage Scheduler
 */
class VScheduledTaskRunner extends VPeriodicWorker
{
	/**
	 * @var array
	 */
	public $_objectEngineTasksCache;

	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::SCHEDULED_TASK;
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
		$maxProfiles = $this->getParams('maxProfiles');

		$profiles = $this->getScheduledTaskProfiles($maxProfiles);
		foreach($profiles as $profile)
		{
			try
			{

				$processor = $this->getProcessor($profile);
				$processor->processProfile($profile);
			}
			catch(Exception $ex)
			{
				VidiunLog::err($ex);
			}
		}
	}

	protected function getProcessor($profile)
	{
		if ($this->isReachProfile($profile))
			return new VReachProcessor($this);
		if ($this->isMediaRepurposingProfile($profile))
			return new VMediaRepurposingProcessor($this);
		else
			return new VGenericProcessor($this);
	}

	private function isMediaRepurposingProfile(VidiunScheduledTaskProfile $profile)
	{
		return ($profile->systemName == "MRP") || (vString::beginsWith($profile->name, 'MR_'));
	}

	private function isReachProfile(VidiunScheduledTaskProfile $profile)
	{
		return $profile->objectFilterEngineType == ObjectFilterEngineType::ENTRY_VENDOR_TASK;
	}

	/**
	 * @param int $maxProfiles
	 * @return array
	 */
	protected function getScheduledTaskProfiles($maxProfiles = 500)
	{
		$scheduledTaskClient = $this->getScheduledTaskClient();

		$filter = new VidiunScheduledTaskProfileFilter();
		$filter->orderBy = VidiunScheduledTaskProfileOrderBy::LAST_EXECUTION_STARTED_AT_ASC;
		$filter->statusEqual = VidiunScheduledTaskProfileStatus::ACTIVE;
		$filter->lastExecutionStartedAtLessThanOrEqualOrNull = strtotime('today');
		$pager = new VidiunFilterPager();
		$pager->pageSize = $maxProfiles;

		$result = $scheduledTaskClient->scheduledTaskProfile->listAction($filter, $pager);
		return $result->objects;
	}

	/**
	 * @return VidiunScheduledTaskClientPlugin
	 */
	public function getScheduledTaskClient()
	{
		$client = $this->getClient();
		return VidiunScheduledTaskClientPlugin::get($client);
	}

	/**
	 * @return VidiunClient
	 */
	public function getClient()
	{
		return self::$vClient;
	}

	public function getParams($name)
	{
		return parent::getParams($name);
	}

}
