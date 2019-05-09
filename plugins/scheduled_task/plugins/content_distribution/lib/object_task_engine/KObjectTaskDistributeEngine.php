<?php

/**
 * @package plugins.scheduledTaskContentDistribution
 * @subpackage lib.objectTaskEngine
 */
class VObjectTaskDistributeEngine extends VObjectTaskEntryEngineBase
{

	/**
	 * @param VidiunBaseEntry $object
	 */
	function processObject($object)
	{
		/** @var VidiunDistributeObjectTask $objectTask */
		$objectTask = $this->getObjectTask();
		if (is_null($objectTask))
			return;

		$entryId = $object->id;
		$distributionProfileId = $objectTask->distributionProfileId;
		if (!$distributionProfileId)
			throw new Exception('Distribution profile id was not configured');

		VidiunLog::info("Trying to distribute entry $entryId with profile $distributionProfileId");

		$client = $this->getClient();
		$contentDistributionPlugin = VidiunContentDistributionClientPlugin::get($client);
		$distributionProfile = $contentDistributionPlugin->distributionProfile->get($distributionProfileId);

		if ($distributionProfile->submitEnabled == VidiunDistributionProfileActionStatus::DISABLED)
			throw new Exception("Submit action for distribution profile $distributionProfileId id disabled");

		$entryDistribution = $this->getEntryDistribution($entryId, $distributionProfileId);
		if ($entryDistribution && $entryDistribution->status == VidiunEntryDistributionStatus::REMOVED)
		{
			VidiunLog::info("Entry distribution is in status REMOVED, deleting it completely");
			$contentDistributionPlugin->entryDistribution->delete($entryDistribution->id);
			$entryDistribution = null;
		}

		if ($entryDistribution)
		{
			VidiunLog::info("Entry distribution already exists with id $entryDistribution->id");
		}
		else
		{
			$entryDistribution = new VidiunEntryDistribution();
			$entryDistribution->distributionProfileId = $distributionProfileId;
			$entryDistribution->entryId = $entryId;
			$entryDistribution = $contentDistributionPlugin->entryDistribution->add($entryDistribution);
		}

		$shouldSubmit = false;
		switch($entryDistribution->status)
		{
			case VidiunEntryDistributionStatus::PENDING:
				$shouldSubmit = true;
				break;
			case VidiunEntryDistributionStatus::QUEUED:
				VidiunLog::info('Entry distribution is already queued');
				break;
			case VidiunEntryDistributionStatus::READY:
				VidiunLog::info('Entry distribution was already submitted');
				break;
			case VidiunEntryDistributionStatus::SUBMITTING:
				VidiunLog::info('Entry distribution is currently being submitted');
				break;
			case VidiunEntryDistributionStatus::UPDATING:
				VidiunLog::info('Entry distribution is currently being updated, so it was submitted already');
				break;
			case VidiunEntryDistributionStatus::DELETING:
				// throwing exception, the task will retry on next execution
				throw new Exception('Entry distribution is currently being deleted and cannot be handled at this stage');
				break;
			case VidiunEntryDistributionStatus::ERROR_SUBMITTING:
			case VidiunEntryDistributionStatus::ERROR_UPDATING:
			case VidiunEntryDistributionStatus::ERROR_DELETING:
				VidiunLog::info('Entry distribution is in error state, trying to resubmit');
				$shouldSubmit = true;
				break;
			case VidiunEntryDistributionStatus::IMPORT_SUBMITTING:
			case VidiunEntryDistributionStatus::IMPORT_UPDATING:
				VidiunLog::info('Entry distribution is waiting for an import job to be finished, do nothing, it will be submitted/updated automatically');
				break;
			default:
				throw new Exception("Entry distribution status $entryDistribution->status is invalid");
		}

		if ($shouldSubmit)
		{
			$contentDistributionPlugin->entryDistribution->submitAdd($entryDistribution->id, true);
		}
	}

	protected function getEntryDistribution($entryId, $distributionProfileId)
	{
		$distributionPlugin = VidiunContentDistributionClientPlugin::get($this->getClient());
		$entryDistributionFilter = new VidiunEntryDistributionFilter();
		$entryDistributionFilter->entryIdEqual = $entryId;
		$entryDistributionFilter->distributionProfileIdEqual = $distributionProfileId;
		$result = $distributionPlugin->entryDistribution->listAction($entryDistributionFilter);
		if (count($result->objects))
			return $result->objects[0];
		else
			return null;
	}
}