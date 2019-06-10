<?php
class vReachFlowManager implements vBatchJobStatusEventConsumer
{
	const ADMIN_CONSOLE_RULE_PREFIX = "AutomaticAdminConsoleRule_";

	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{
		if ($dbBatchJob->getJobType() == BatchJobType::COPY_PARTNER &&  $dbBatchJob->getStatus() == BatchJob::BATCHJOB_STATUS_FINISHED)
			return true;

		return false;
	}

	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::updatedJob()
	 */
	public function updatedJob(BatchJob $dbBatchJob)
	{
		if ($dbBatchJob->getJobType() == BatchJobType::COPY_PARTNER &&  $dbBatchJob->getStatus() == BatchJob::BATCHJOB_STATUS_FINISHED)
		{
			return $this->handleCopyReachDataToPartner($dbBatchJob);
		}
	}


	/**
	 * @param BatchJob $dbBatchJob
	 * @return bool
	 */
	protected function handleCopyReachDataToPartner(BatchJob $dbBatchJob)
	{
		/** @var $dbBatchJob vCopyPartnerJobData */
		$fromPartnerId = $dbBatchJob->getData()->getFromPartnerId();
		$toPartnerId = $dbBatchJob->getData()->getToPartnerId();

		if (!ReachPlugin::isAllowedPartner($fromPartnerId) || !ReachPlugin::isAllowedPartner($toPartnerId))
		{
			VidiunLog::info("Skip copying reach data from partner [$fromPartnerId] to partner [$toPartnerId]. Reach plugin is not enabled");
			return true;
		}

		VidiunLog::info("Start Copying Active ReachProfiles and PartnerCatalogItems from partner [$fromPartnerId]: to partner [$toPartnerId]");
		$reachProfiles = ReachProfilePeer::retrieveByPartnerId($fromPartnerId);
		foreach ($reachProfiles as $profile)
		{
			/* @var $profile ReachProfile */
			$newReachProfiles = $profile->copy();
			$newReachProfiles->setPartnerId($toPartnerId);
			$rules = $newReachProfiles->getRulesArray();
			foreach ( $rules as $key => $rule )
			{
				/* @var vrule $rule*/
				$description = $rule->getDescription();
				if (empty($description)
					|| substr($rule->getDescription(), 0, strlen(self::ADMIN_CONSOLE_RULE_PREFIX)) !== self::ADMIN_CONSOLE_RULE_PREFIX)
				{
					unset($rules[$key]);
				}
			}
			$newReachProfiles->setRulesArray($rules);
			$newReachProfiles->save();
		}

		$catalogItems = PartnerCatalogItemPeer::retrieveActiveCatalogItems($fromPartnerId);
		foreach ($catalogItems as $catalogItem)
		{
			/* @var $catalogItem PartnerCatalogItem */
			$newCatalogItem = $catalogItem->copy();
			$newCatalogItem->setPartnerId($toPartnerId);
			$newCatalogItem->save();
		}

		return true;
	}
}