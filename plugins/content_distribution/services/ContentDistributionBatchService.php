<?php
/**
 * @service contentDistributionBatch
 * @package plugins.contentDistribution
 * @subpackage api.services
 */
class ContentDistributionBatchService extends VidiunBaseService
{
	const FIVE_MINUTES_IN_SECONDS = 300;

// --------------------------------- Distribution Synchronizer functions 	--------------------------------- //

	/**
	 * updates entry distribution sun status in the search engine
	 *
	 * @action updateSunStatus
	 */
	function updateSunStatusAction()
	{
		vApiCache::setConditionalCacheExpiry(self::FIVE_MINUTES_IN_SECONDS);

		$updatedEntries = array();
		
		// serach all records that their sun status changed to after sunset
		$criteria = VidiunCriteria::create(EntryDistributionPeer::OM_CLASS);
		$criteria->add(EntryDistributionPeer::STATUS, EntryDistributionStatus::READY);
		$criteria->add(EntryDistributionPeer::SUN_STATUS, EntryDistributionSunStatus::AFTER_SUNSET , Criteria::NOT_EQUAL);
		$crit1 = $criteria->getNewCriterion(EntryDistributionPeer::SUNSET, vApiCache::getTime(), Criteria::LESS_THAN);
		$criteria->add($crit1);
		$entryDistributions = EntryDistributionPeer::doSelect($criteria);
		foreach($entryDistributions as $entryDistribution)
		{
			/* @var $entryDistribution EntryDistribution */
			$entryId = $entryDistribution->getEntryId();
			if(isset($updatedEntries[$entryId]))
				continue;
				
			$updatedEntries[$entryId] = true;
			vEventsManager::raiseEvent(new vObjectUpdatedEvent($entryDistribution)); // raise the updated events to trigger index in search engine (sphinx)
			vEventsManager::flushEvents(); // save entry changes to sphinx
			vMemoryManager::clearMemory();
		}

		$updatedEntries = array();

		// serach all records that their sun status changed to after sunrise
		$criteria = VidiunCriteria::create(EntryDistributionPeer::OM_CLASS);
		$criteria->add(EntryDistributionPeer::STATUS, EntryDistributionStatus::QUEUED);
		$criteria->add(EntryDistributionPeer::SUN_STATUS, EntryDistributionSunStatus::BEFORE_SUNRISE);
		$criteria->add(EntryDistributionPeer::SUNRISE, vApiCache::getTime(), Criteria::LESS_THAN);
		$entryDistributions = EntryDistributionPeer::doSelect($criteria);
		foreach($entryDistributions as $entryDistribution)
		{
			/* @var $entryDistribution EntryDistribution */
			$entryId = $entryDistribution->getEntryId();
			if(isset($updatedEntries[$entryId]))
				continue;
				
			$updatedEntries[$entryId] = true;
			vEventsManager::raiseEvent(new vObjectUpdatedEvent($entryDistribution)); // raise the updated events to trigger index in search engine (sphinx)
			vEventsManager::flushEvents(); // save entry changes to sphinx
			vMemoryManager::clearMemory();
		}
	}


	/**
	 * creates all required jobs according to entry distribution dirty flags
	 *
	 * @action createRequiredJobs
	 */
	function createRequiredJobsAction()
	{
		// serach all records that their next report time arrived
		$criteria = VidiunCriteria::create(EntryDistributionPeer::OM_CLASS);
		$criteria->add(EntryDistributionPeer::NEXT_REPORT, time(), Criteria::LESS_EQUAL);
		$entryDistributions = EntryDistributionPeer::doSelect($criteria);
		foreach($entryDistributions as $entryDistribution)
		{
			$distributionProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->getDistributionProfileId());
			if($distributionProfile)
				vContentDistributionManager::submitFetchEntryDistributionReport($entryDistribution, $distributionProfile);
			else
				VidiunLog::err("Distribution profile [" . $entryDistribution->getDistributionProfileId() . "] not found for entry distribution [" . $entryDistribution->getId() . "]");
		}


		// serach all records that arrived their sunrise time and requires submittion
		$criteria = VidiunCriteria::create(EntryDistributionPeer::OM_CLASS);
		$criteria->add(EntryDistributionPeer::DIRTY_STATUS, EntryDistributionDirtyStatus::SUBMIT_REQUIRED);
		$criteria->add(EntryDistributionPeer::SUNRISE, time(), Criteria::LESS_EQUAL);
		$entryDistributions = EntryDistributionPeer::doSelect($criteria);
		foreach($entryDistributions as $entryDistribution)
		{
			$distributionProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->getDistributionProfileId());
			if($distributionProfile)
				vContentDistributionManager::submitAddEntryDistribution($entryDistribution, $distributionProfile);
			else
				VidiunLog::err("Distribution profile [" . $entryDistribution->getDistributionProfileId() . "] not found for entry distribution [" . $entryDistribution->getId() . "]");
		}


		// serach all records that arrived their sunrise time and requires enable
		$criteria = VidiunCriteria::create(EntryDistributionPeer::OM_CLASS);
		$criteria->add(EntryDistributionPeer::DIRTY_STATUS, EntryDistributionDirtyStatus::ENABLE_REQUIRED);
		$criteria->add(EntryDistributionPeer::SUNRISE, time(), Criteria::LESS_EQUAL);
		$entryDistributions = EntryDistributionPeer::doSelect($criteria);
		foreach($entryDistributions as $entryDistribution)
		{
			$distributionProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->getDistributionProfileId());
			if($distributionProfile)
				vContentDistributionManager::submitEnableEntryDistribution($entryDistribution, $distributionProfile);
			else
				VidiunLog::err("Distribution profile [" . $entryDistribution->getDistributionProfileId() . "] not found for entry distribution [" . $entryDistribution->getId() . "]");
		}


		// serach all records that arrived their sunset time and requires deletion
		$criteria = VidiunCriteria::create(EntryDistributionPeer::OM_CLASS);
		$criteria->add(EntryDistributionPeer::DIRTY_STATUS, EntryDistributionDirtyStatus::DELETE_REQUIRED);
		$criteria->add(EntryDistributionPeer::SUNSET, time(), Criteria::LESS_EQUAL);
		$entryDistributions = EntryDistributionPeer::doSelect($criteria);
		foreach($entryDistributions as $entryDistribution)
		{
			$distributionProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->getDistributionProfileId());
			if($distributionProfile)
				vContentDistributionManager::submitDeleteEntryDistribution($entryDistribution, $distributionProfile);
			else
				VidiunLog::err("Distribution profile [" . $entryDistribution->getDistributionProfileId() . "] not found for entry distribution [" . $entryDistribution->getId() . "]");
		}


		// serach all records that arrived their sunset time and requires disable
		$criteria = VidiunCriteria::create(EntryDistributionPeer::OM_CLASS);
		$criteria->add(EntryDistributionPeer::DIRTY_STATUS, EntryDistributionDirtyStatus::DISABLE_REQUIRED);
		$criteria->add(EntryDistributionPeer::SUNSET, time(), Criteria::LESS_EQUAL);
		$entryDistributions = EntryDistributionPeer::doSelect($criteria);
		foreach($entryDistributions as $entryDistribution)
		{
			$distributionProfile = DistributionProfilePeer::retrieveByPK($entryDistribution->getDistributionProfileId());
			if($distributionProfile)
				vContentDistributionManager::submitDisableEntryDistribution($entryDistribution, $distributionProfile);
			else
				VidiunLog::err("Distribution profile [" . $entryDistribution->getDistributionProfileId() . "] not found for entry distribution [" . $entryDistribution->getId() . "]");
		}
	}


// --------------------------------- Distribution Synchronizer functions 	--------------------------------- //


	/**
	 * returns absolute valid url for asset file
	 *
	 * @action getAssetUrl
	 * @param string $assetId
	 * @return string
	 * @throws VidiunErrors::INVALID_OBJECT_ID
	 * @throws VidiunErrors::FLAVOR_ASSET_IS_NOT_READY
	 * @throws VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND
	 */
	function getAssetUrlAction($assetId)
	{
		$asset = assetPeer::retrieveById($assetId);
		if(!$asset)
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_ID, $assetId);

		$ext = $asset->getFileExt();
		if(is_null($ext))
			$ext = 'jpg';

		$fileName = $asset->getEntryId() . "_" . $asset->getId() . ".$ext";

		$syncKey = $asset->getSyncKey(asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
		if(!vFileSyncUtils::fileSync_exists($syncKey))
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_IS_NOT_READY, $asset->getId());

		list($fileSync, $local) = vFileSyncUtils::getReadyFileSyncForKey($syncKey, true, false, false);
		if(!$fileSync)
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $asset->getId());

		return $fileSync->getExternalUrl($asset->getEntryId());
	}
}
