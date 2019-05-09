<?php

/**
 * Base class for live streams and live channels
 *
 * @service liveStream
 * @package api
 * @subpackage services
 */
class VidiunLiveEntryService extends VidiunEntryService
{
	//amount of time for attempting to grab vLock
	const VLOCK_CREATE_RECORDED_ENTRY_GRAB_TIMEOUT = 0.1;

	//amount of time for holding vLock
	const VLOCK_CREATE_RECORDED_ENTRY_HOLD_TIMEOUT = 3;

	//Max time from recording created time before creating new recorded entry
	const SEVEN_DAYS_IN_SECONDS = 604800;

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		$allowedSystemPartners = array(
			Partner::BATCH_PARTNER_ID,
			Partner::MEDIA_SERVER_PARTNER_ID,
		);

		// Allow bacth and media server partner to list all partner entries
		if (in_array($this->getPartnerId(), $allowedSystemPartners) && in_array($actionName, array('list', 'get')))
			myPartnerUtils::resetPartnerFilter('entry');

		if (in_array($this->getPartner()->getStatus(), array(Partner::PARTNER_STATUS_CONTENT_BLOCK, Partner::PARTNER_STATUS_FULL_BLOCK)))
		{
			throw new vCoreException("Partner blocked", vCoreException::PARTNER_BLOCKED);
		}
	}


	protected function partnerRequired($actionName)
	{
		if ($actionName === 'isLive')
		{
			return false;
		}
		return parent::partnerRequired($actionName);
	}

	function dumpApiRequest($entryId, $onlyIfAvailable = true)
	{
		$entryDc = substr($entryId, 0, 1);
		if ($entryDc != vDataCenterMgr::getCurrentDcId())
		{
			$remoteDCHost = vDataCenterMgr::getRemoteDcExternalUrlByDcId($entryDc);
			vFileUtils::dumpApiRequest($remoteDCHost, $onlyIfAvailable);
		}
	}

	/**
	 * Append recorded video to live entry
	 *
	 * @action appendRecording
	 * @param string $entryId Live entry id
	 * @param string $assetId Live asset id
	 * @param VidiunEntryServerNodeType $mediaServerIndex
	 * @param VidiunDataCenterContentResource $resource
	 * @param float $duration in seconds
	 * @param bool $isLastChunk Is this the last recorded chunk in the current session (i.e. following a stream stop event)
	 * @return VidiunLiveEntry The updated live entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	function appendRecordingAction($entryId, $assetId, $mediaServerIndex, VidiunDataCenterContentResource $resource, $duration, $isLastChunk = false)
	{
		if (PermissionPeer::isValidForPartner(PermissionName::FEATURE_LIVE_STREAM_VIDIUN_RECORDING, vCurrentContext::getCurrentPartnerId()))
		{
			throw new VidiunAPIException(VidiunErrors::VIDIUN_RECORDING_ENABLED, vCurrentContext::$partner_id);
		}

		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry || !($dbEntry instanceof LiveEntry))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$dbAsset = assetPeer::retrieveById($assetId);
		if (!$dbAsset || !($dbAsset instanceof liveAsset))
			throw new VidiunAPIException(VidiunErrors::ASSET_ID_NOT_FOUND, $assetId);

		$maxRecordingDuration = (vConf::get('max_live_recording_duration_hours') + 1) * 60 * 60 * 1000;
		$currentDuration = $dbEntry->getCurrentDuration($duration, $maxRecordingDuration);
		if ($currentDuration > $maxRecordingDuration)
		{
			throw new VidiunAPIException(VidiunErrors::LIVE_STREAM_EXCEEDED_MAX_RECORDED_DURATION, $entryId);
		}

		$vResource = $resource->toObject();
		$filename = $vResource->getLocalFilePath();
		if (!($resource instanceof VidiunServerFileResource))
		{
			$filename = vConf::get('uploaded_segment_destination') . basename($vResource->getLocalFilePath());
			vFile::moveFile($vResource->getLocalFilePath(), $filename);
			chgrp($filename, vConf::get('content_group'));
			chmod($filename, 0640);
		}

		if ($dbAsset->hasTag(assetParams::TAG_RECORDING_ANCHOR) && $mediaServerIndex == EntryServerNodeType::LIVE_PRIMARY)
		{
			$dbEntry->setLengthInMsecs($currentDuration);

			if ($isLastChunk)
			{
				// Save last elapsed recording time
				$dbEntry->setLastElapsedRecordingTime($currentDuration);
			}

			$dbEntry->save();
		}

		vJobsManager::addConvertLiveSegmentJob(null, $dbAsset, $mediaServerIndex, $filename, $currentDuration);

		if ($mediaServerIndex == EntryServerNodeType::LIVE_PRIMARY)
		{
			if (!$dbEntry->getRecordedEntryId())
			{
				$this->createRecordedEntry($dbEntry, $mediaServerIndex);
			}

			$recordedEntry = entryPeer::retrieveByPK($dbEntry->getRecordedEntryId());
			if ($recordedEntry)
			{
				if ($recordedEntry->getSourceType() !== EntrySourceType::RECORDED_LIVE)
				{
					$recordedEntry->setSourceType(EntrySourceType::RECORDED_LIVE);
					$recordedEntry->save();
				}
				$this->ingestAsset($recordedEntry, $dbAsset, $filename);
			}
		}

		$entry = VidiunEntryFactory::getInstanceByType($dbEntry->getType());
		$entry->fromObject($dbEntry, $this->getResponseProfile());
		return $entry;
	}

	private function ingestAsset(entry $entry, $dbAsset, $filename, $shouldCopy = true, $flavorParamsId = null)
	{
		if ($dbAsset)
			$flavorParamsId = $dbAsset->getFlavorParamsId();
		$flavorParams = assetParamsPeer::retrieveByPKNoFilter($flavorParamsId);

		// is first chunk
		$recordedAsset = assetPeer::retrieveByEntryIdAndParams($entry->getId(), $flavorParamsId);
		if ($recordedAsset)
		{
			VidiunLog::info("Asset [" . $recordedAsset->getId() . "] of flavor params id [$flavorParamsId] already exists");
			return;
		}

		// create asset
		$recordedAsset = assetPeer::getNewAsset(assetType::FLAVOR);
		$recordedAsset->setPartnerId($entry->getPartnerId());
		$recordedAsset->setEntryId($entry->getId());
		$recordedAsset->setStatus(asset::FLAVOR_ASSET_STATUS_QUEUED);
		$recordedAsset->setFlavorParamsId($flavorParams->getId());
		$recordedAsset->setFromAssetParams($flavorParams);
		$recordedAsset->incrementVersion();
		if ($dbAsset && $dbAsset->hasTag(assetParams::TAG_RECORDING_ANCHOR))
		{
			$recordedAsset->addTags(array(assetParams::TAG_RECORDING_ANCHOR));
		}

		if ($flavorParams->hasTag(assetParams::TAG_SOURCE))
		{
			$recordedAsset->setIsOriginal(true);
		}

		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if ($ext)
		{
			$recordedAsset->setFileExt($ext);
		}

		$recordedAsset->save();

		// create file sync
		$recordedAssetKey = $recordedAsset->getSyncKey(flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		vFileSyncUtils::moveFromFile($filename, $recordedAssetKey, true, $shouldCopy);

		vEventsManager::raiseEvent(new vObjectAddedEvent($recordedAsset));
	}

	/**
	 * Register media server to live entry
	 *
	 * @action registerMediaServer
	 * @param string $entryId Live entry id
	 * @param string $hostname Media server host name
	 * @param VidiunEntryServerNodeType $mediaServerIndex Media server index primary / secondary
	 * @param string $applicationName the application to which entry is being broadcast
	 * @param VidiunEntryServerNodeStatus $liveEntryStatus the status VidiunEntryServerNodeStatus::PLAYABLE | VidiunEntryServerNodeStatus::BROADCASTING
	 * @param bool $shouldCreateRecordedEntry
	 * @return VidiunLiveEntry The updated live entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::SERVER_NODE_NOT_FOUND
	 * @throws VidiunErrors::ENTRY_SERVER_NODE_MULTI_RESULT
	 */
	function registerMediaServerAction($entryId, $hostname, $mediaServerIndex, $applicationName = null, $liveEntryStatus = VidiunEntryServerNodeStatus::PLAYABLE, $shouldCreateRecordedEntry = true)
	{
		vApiCache::disableConditionalCache();
		VidiunLog::debug("Entry [$entryId] from mediaServerIndex [$mediaServerIndex] with liveEntryStatus [$liveEntryStatus]");

		$dbLiveEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbLiveEntry || !($dbLiveEntry instanceof LiveEntry))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$this->setMediaServerWrapper($dbLiveEntry, $mediaServerIndex, $hostname, $liveEntryStatus, $applicationName);

		// setRedirectEntryId to null in all cases, even for broadcasting...

		$dbLiveEntry->save();
		return $this->checkAndCreateRecordedEntry($dbLiveEntry, $mediaServerIndex, $liveEntryStatus, true, $shouldCreateRecordedEntry);
	}

	protected function setMediaServerWrapper($dbLiveEntry, $mediaServerIndex, $hostname, $liveEntryStatus, $applicationName)
	{
		/* @var $dbLiveEntry LiveEntry */
		try
		{
			$dbLiveEntry->setMediaServer($mediaServerIndex, $hostname, $liveEntryStatus, $applicationName);
		} catch (vCoreException $ex)
		{
			$code = $ex->getCode();
			switch ($code)
			{
				case vCoreException::MEDIA_SERVER_NOT_FOUND :
					throw new VidiunAPIException(VidiunErrors::MEDIA_SERVER_NOT_FOUND, $hostname);
				default:
					throw $ex;
			}
		}
	}

	/**
	 * @param LiveEntry $dbEntry
	 * @param EntryServerNodeType $mediaServerIndex
	 * @return entry
	 * @throws Exception
	 * @throws PropelException
	 */
	private function createRecordedEntry(LiveEntry $dbEntry, $mediaServerIndex)
	{
		$lock = vLock::create("live_record_" . $dbEntry->getId());

		if ($lock && !$lock->lock(self::VLOCK_CREATE_RECORDED_ENTRY_GRAB_TIMEOUT, self::VLOCK_CREATE_RECORDED_ENTRY_HOLD_TIMEOUT))
		{
			return;
		}

		// If while we were waiting for the lock, someone has updated the recorded entry id - we should use it.
		$dbEntry->reload();
		if (($dbEntry->getRecordStatus() != RecordStatus::PER_SESSION) && ($dbEntry->getRecordedEntryId()))
		{
			$recordedEntry = entryPeer::retrieveByPK($dbEntry->getRecordedEntryId());
			if ($recordedEntry)
			{
				$lock->unlock();
				return $recordedEntry;
			}
		}

		$recordedEntry = null;
		try
		{
			$recordedEntryName = $dbEntry->getName();
			if ($dbEntry->getRecordStatus() == RecordStatus::PER_SESSION)
				$recordedEntryName .= ' ' . ($dbEntry->getRecordedEntryIndex() + 1);

			$recordedEntry = new entry();
			$recordedEntry->setType(entryType::MEDIA_CLIP);
			$recordedEntry->setMediaType(entry::ENTRY_MEDIA_TYPE_VIDEO);
			$recordedEntry->setRootEntryId($dbEntry->getId());
			$recordedEntry->setName($recordedEntryName);
			$recordedEntry->setDescription($dbEntry->getDescription());
			$recordedEntry->setSourceType(EntrySourceType::VIDIUN_RECORDED_LIVE);
			$recordedEntry->setAccessControlId($dbEntry->getAccessControlId());
			$recordedEntry->setVuserId($dbEntry->getVuserId());
			$recordedEntry->setPartnerId($dbEntry->getPartnerId());
			$recordedEntry->setModerationStatus($dbEntry->getModerationStatus());
			$recordedEntry->setIsRecordedEntry(true);
			$recordedEntry->setTags($dbEntry->getTags());
			$recordedEntry->setStatus(entryStatus::NO_CONTENT);
			$recordedEntry->setConversionProfileId($dbEntry->getConversionProfileId());

			// make the recorded entry to be "hidden" in search so it won't return in entry list action
			if ($dbEntry->getRecordingOptions() && $dbEntry->getRecordingOptions()->getShouldMakeHidden())
			{
				$recordedEntry->setDisplayInSearch(EntryDisplayInSearchType::SYSTEM);
			}
			if ($dbEntry->getRecordingOptions() && $dbEntry->getRecordingOptions()->getShouldCopyScheduling())
			{
				$recordedEntry->setStartDate($dbEntry->getStartDate());
				$recordedEntry->setEndDate($dbEntry->getEndDate());
			}

			$recordedEntry->save();

			$dbEntry->setRecordedEntryId($recordedEntry->getId());
			$dbEntry->save();

			$assets = assetPeer::retrieveByEntryId($dbEntry->getId(), array(assetType::LIVE));
			foreach ($assets as $asset)
			{
				/* @var $asset liveAsset */
				$asset->incLiveSegmentVersion($mediaServerIndex);
				$asset->save();
			}
		} catch (Exception $e)
		{
			$lock->unlock();
			throw $e;
		}

		if ($lock)
		{
			$lock->unlock();
		}

		return $recordedEntry;
	}

	/**
	 * Unregister media server from live entry
	 *
	 * @action unregisterMediaServer
	 * @param string $entryId Live entry id
	 * @param string $hostname Media server host name
	 * @param VidiunEntryServerNodeType $mediaServerIndex Media server index primary / secondary
	 * @return VidiunLiveEntry The updated live entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::SERVER_NODE_NOT_FOUND
	 * @throws VidiunErrors::ENTRY_SERVER_NODE_MULTI_RESULT
	 */
	function unregisterMediaServerAction($entryId, $hostname, $mediaServerIndex)
	{
		$this->dumpApiRequest($entryId, true);

		VidiunLog::debug("Entry [$entryId] from mediaServerIndex [$mediaServerIndex] with hostname [$hostname]");

		/* @var $dbLiveEntry LiveEntry */
		$dbLiveEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbLiveEntry || !($dbLiveEntry instanceof LiveEntry))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$dbServerNode = ServerNodePeer::retrieveActiveMediaServerNode($hostname);
		if (!$dbServerNode)
			throw new VidiunAPIException(VidiunErrors::SERVER_NODE_NOT_FOUND, $hostname);

		$dbLiveEntryServerNode = EntryServerNodePeer::retrieveByEntryIdAndServerType($entryId, $mediaServerIndex);
		if (!$dbLiveEntryServerNode)
			throw new VidiunAPIException(VidiunErrors::ENTRY_SERVER_NODE_NOT_FOUND, $entryId, $mediaServerIndex);

		$dbLiveEntryServerNode->deleteOrMarkForDeletion();

		$entry = VidiunEntryFactory::getInstanceByType($dbLiveEntry->getType());
		$entry->fromObject($dbLiveEntry, $this->getResponseProfile());
		return $entry;
	}

	/**
	 * Validates all registered media servers
	 *
	 * @action validateRegisteredMediaServers
	 * @param string $entryId Live entry id
	 *
	 * @throws VidiunAPIException
	 */
	function validateRegisteredMediaServersAction($entryId)
	{
		VidiunResponseCacher::disableCache();

		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry || !($dbEntry instanceof LiveEntry))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		/* @var $dbEntry LiveEntry */
		$dbEntry->validateMediaServers();
	}

	/**
	 * Set recorded video to live entry
	 *
	 * @action setRecordedContent
	 * @param string $entryId Live entry id
	 * @param VidiunEntryServerNodeType $mediaServerIndex
	 * @param VidiunDataCenterContentResource $resource
	 * @param float $duration in seconds
	 * @param string $recordedEntryId Recorded entry Id
	 * @param int $flavorParamsId Recorded entry Id
	 * @return VidiunLiveEntry The updated live entry
	 *
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::RECORDED_ENTRY_LIVE_MISMATCH
	 */
	function setRecordedContentAction($entryId, $mediaServerIndex, VidiunDataCenterContentResource $resource, $duration, $recordedEntryId = null, $flavorParamsId = null)
	{
		if (!PermissionPeer::isValidForPartner(PermissionName::FEATURE_LIVE_STREAM_VIDIUN_RECORDING, vCurrentContext::getCurrentPartnerId()))
		{
			throw new VidiunAPIException(VidiunErrors::VIDIUN_RECORDING_DISABLED, vCurrentContext::$partner_id);
		}

		$dbLiveEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbLiveEntry || !($dbLiveEntry instanceof LiveEntry))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		if ($mediaServerIndex != EntryServerNodeType::LIVE_PRIMARY)
		{
			$entry = VidiunEntryFactory::getInstanceByType($dbLiveEntry->getType());
			$entry->fromObject($dbLiveEntry, $this->getResponseProfile());
			return $entry;
		}

		$recordedEntry = null;
		$createRecordedEntry = false;
		if ($recordedEntryId)
		{
			$recordedEntry = entryPeer::retrieveByPK($recordedEntryId);
			if ($recordedEntry && $recordedEntry->getRootEntryId() != $entryId)
				throw new VidiunAPIException(VidiunErrors::RECORDED_ENTRY_LIVE_MISMATCH, $entryId, $recordedEntryId);

			if ($recordedEntry && $recordedEntry->getSourceType() != EntrySourceType::VIDIUN_RECORDED_LIVE)
			{
				$recordedEntry = null;
				$createRecordedEntry = true;
				$dbLiveEntry->setRecordedEntryId(null);
				$dbLiveEntry->save();
			}
		} else if ($dbLiveEntry->getRecordedEntryId())
		{
			$recordedEntry = entryPeer::retrieveByPK($dbLiveEntry->getRecordedEntryId());
			if (!$recordedEntry)
				$createRecordedEntry = true;
		} else
		{
			$createRecordedEntry = true;
		}

		if ($createRecordedEntry)
			$recordedEntry = $this->createRecordedEntry($dbLiveEntry, $mediaServerIndex);

		if (!$recordedEntry)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $recordedEntryId);

		if ($recordedEntry->getFlowType() != EntryFlowType::LIVE_CLIPPING)
		{
			$totalDuration = (int)($duration * 1000);
			$dbLiveEntry->setLengthInMsecs($totalDuration);
			$dbLiveEntry->save();
		}

		$this->handleRecording($dbLiveEntry, $recordedEntry, $resource, $flavorParamsId);

		$entry = VidiunEntryFactory::getInstanceByType($dbLiveEntry->getType());
		$entry->fromObject($dbLiveEntry, $this->getResponseProfile());
		return $entry;
	}

	private function handleRecording(LiveEntry $dbLiveEntry, entry $recordedEntry, VidiunDataCenterContentResource $resource, $flavorParamsId = null)
	{
		if (!$flavorParamsId)
		{
			$service = new MediaService();
			$service->initService('media', 'media', 'updateContent');
			$service->updateContentAction($recordedEntry->getId(), $resource);
			return;
		}

		//In case conversion profile was changed we need to fetch passed streamed assets as well
		$dbAsset = assetPeer::retrieveByEntryIdAndParamsNoFilter($dbLiveEntry->getId(), $flavorParamsId);
		if (!$dbAsset)
		{
			$flavorParamConversionProfile = flavorParamsConversionProfilePeer::retrieveByFlavorParamsAndConversionProfile($flavorParamsId, $dbLiveEntry->getConversionProfileId());
			if (!$flavorParamConversionProfile)
				throw new VidiunAPIException(VidiunErrors::FLAVOR_PARAMS_ID_NOT_FOUND, $flavorParamsId);
		}

		$vResource = $resource->toObject();
		/* @var $vResource vLocalFileResource */
		$filename = $vResource->getLocalFilePath();
		$keepOriginalFile = $vResource->getKeepOriginalFile();

		$lockKey = "create_replacing_entry_" . $recordedEntry->getId();
		$replacingEntry = vLock::runLocked($lockKey, array('vFlowHelper', 'getReplacingEntry'), array($recordedEntry, $dbAsset, 0, $flavorParamsId));
		$this->ingestAsset($replacingEntry, $dbAsset, $filename, $keepOriginalFile, $flavorParamsId);
	}

	/**
	 * Create recorded entry id if it doesn't exist and make sure it happens on the DC that the live entry was created on.
	 * @action createRecordedEntry
	 * @param string $entryId Live entry id
	 * @param VidiunEntryServerNodeType $mediaServerIndex Media server index primary / secondary
	 * @param VidiunEntryServerNodeStatus $liveEntryStatus the status VidiunEntryServerNodeStatus::PLAYABLE | VidiunEntryServerNodeStatus::BROADCASTING
	 * @return VidiunLiveEntry The updated live entry
	 * @throws VidiunAPIException
	 */
	public function createRecordedEntryAction($entryId, $mediaServerIndex, $liveEntryStatus)
	{
		$this->dumpApiRequest($entryId, true);
		$dbLiveEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbLiveEntry || !($dbLiveEntry instanceof LiveEntry))
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		return $this->checkAndCreateRecordedEntry($dbLiveEntry, $mediaServerIndex, $liveEntryStatus, false);

	}

	protected function checkAndCreateRecordedEntry($dbLiveEntry, $mediaServerIndex, $liveEntryStatus, $forcePrimaryValidation, $shouldCreateRecordedEntry = true)
	{
		if ($shouldCreateRecordedEntry && (!$forcePrimaryValidation || $mediaServerIndex == EntryServerNodeType::LIVE_PRIMARY) &&
			in_array($liveEntryStatus, array(EntryServerNodeStatus::BROADCASTING, EntryServerNodeStatus::PLAYABLE)) &&
			$dbLiveEntry->getRecordStatus()
		)
		{
			VidiunLog::info("Checking if recorded entry needs to be created for entry ".$dbLiveEntry->getId());
			$createRecordedEntry = false;
			if(!$dbLiveEntry->getRecordedEntryId())
			{
				$createRecordedEntry = true;
				VidiunLog::info("Creating a new recorded entry for ".$dbLiveEntry->getId());
			}
			else {
				$dbRecordedEntry = entryPeer::retrieveByPK($dbLiveEntry->getRecordedEntryId());
				if (!$dbRecordedEntry) {
					$createRecordedEntry = true;
				}
				else{
					$recordedEntryCreationTime = $dbRecordedEntry->getCreatedAt(null);

					$isNewSession = $dbLiveEntry->getLastBroadcastEndTime() + vConf::get('live_session_reconnect_timeout', 'local', 180) < $dbLiveEntry->getCurrentBroadcastStartTime();
					$recordedEntryNotYetCreatedForCurrentSession = $recordedEntryCreationTime < $dbLiveEntry->getCurrentBroadcastStartTime();
					$maxAppendTimeReached = ($recordedEntryCreationTime + self::SEVEN_DAYS_IN_SECONDS) < time();

					VidiunLog::debug("isNewSession [$isNewSession] getLastBroadcastEndTime [{$dbLiveEntry->getLastBroadcastEndTime()}] getCurrentBroadcastStartTime [{$dbLiveEntry->getCurrentBroadcastStartTime()}]");
					VidiunLog::debug("recordedEntryCreationTime [$recordedEntryNotYetCreatedForCurrentSession] recordedEntryCreationTime [$recordedEntryCreationTime] getCurrentBroadcastStartTime [{$dbLiveEntry->getCurrentBroadcastStartTime()}]");
					VidiunLog::debug("maxAppendTimeReached [$maxAppendTimeReached] recordedEntryCreationTime [$recordedEntryCreationTime]");

					if ($dbLiveEntry->getRecordStatus() == RecordStatus::PER_SESSION && $isNewSession && $recordedEntryNotYetCreatedForCurrentSession)
					{
						$createRecordedEntry = true;
					}

					if($dbLiveEntry->getRecordStatus() == RecordStatus::APPENDED && $dbRecordedEntry->getSourceType() == EntrySourceType::VIDIUN_RECORDED_LIVE && $maxAppendTimeReached)
					{
						$createRecordedEntry = true;
						$dbLiveEntry->setRecordedEntryId(null);
						$dbLiveEntry->save();
					}
				}
			}
			if ($createRecordedEntry)
			{
				VidiunLog::info("Creating a recorded entry for ".$dbLiveEntry->getId());
				$this->createRecordedEntry($dbLiveEntry, $mediaServerIndex);
			}
		}

		$entry = VidiunEntryFactory::getInstanceByType($dbLiveEntry->getType());
		$entry->fromObject($dbLiveEntry, $this->getResponseProfile());
		return $entry;
	}
}
