<?php

/**
 * Live Stream service lets you manage live stream entries
 *
 * @service liveStream
 * @package api
 * @subpackage services
 */
class LiveStreamService extends VidiunLiveEntryService
{
	const ISLIVE_ACTION_CACHE_EXPIRY_WHEN_NOT_LIVE = 10;
	const ISLIVE_ACTION_CACHE_EXPIRY_WHEN_LIVE = 30;
	const ISLIVE_ACTION_NON_VIDIUN_LIVE_CONDITIONAL_CACHE_EXPIRY = 10;
	const HLS_LIVE_STREAM_CONTENT_TYPE = 'application/vnd.apple.mpegurl';

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		if($this->getPartnerId() > 0 && !PermissionPeer::isValidForPartner(PermissionName::FEATURE_LIVE_STREAM, $this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN, $this->serviceName.'->'.$this->actionName);
	}
	
	
	protected function partnerRequired($actionName)
	{
		if ($actionName === 'isLive') {
			return false;
		}
		return parent::partnerRequired($actionName);
	}
	
	/**
	 * Adds new live stream entry.
	 * The entry will be queued for provision.
	 * 
	 * @action add
	 * @param VidiunLiveStreamEntry $liveStreamEntry Live stream entry metadata  
	 * @param VidiunSourceType $sourceType  Live stream source type
	 * @return VidiunLiveStreamEntry The new live stream entry
	 * 
	 * @throws VidiunErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL
	 */
	function addAction(VidiunLiveStreamEntry $liveStreamEntry, $sourceType = null)
	{
		if($sourceType) {
			$liveStreamEntry->sourceType = $sourceType;	
		}
		elseif(is_null($liveStreamEntry->sourceType)) {
			// default sourceType is AKAMAI_LIVE
			$liveStreamEntry->sourceType = vPluginableEnumsManager::coreToApi('EntrySourceType', $this->getPartner()->getDefaultLiveStreamEntrySourceType());
		}
	
		$conversionProfileId = null;
		if(in_array($liveStreamEntry->sourceType, array(VidiunSourceType::LIVE_STREAM, VidiunSourceType::LIVE_STREAM_ONTEXTDATA_CAPTIONS)))
		{
			$conversionProfileId = $liveStreamEntry->conversionProfileId;
			if(!$conversionProfileId)
			{
				$partner = $this->getPartner();
				if($partner)
					$conversionProfileId = $partner->getDefaultLiveConversionProfileId();
			}
		}
	
		$dbEntry = $this->duplicateTemplateEntry($conversionProfileId, $liveStreamEntry->templateEntryId, new LiveStreamEntry());
		$dbEntry = $this->prepareEntryForInsert($liveStreamEntry, $dbEntry);
		$dbEntry->save();
		
		$te = new TrackEntry();
		$te->setEntryId($dbEntry->getId());
		$te->setTrackEventTypeId(TrackEntry::TRACK_ENTRY_EVENT_TYPE_ADD_ENTRY);
		$te->setDescription(__METHOD__ . ":" . __LINE__ . "::" . $dbEntry->getSource());
		TrackEntry::addTrackEntry($te);
		
		//If a jobData can be created for entry sourceType, add provision job. Otherwise, just save the entry.
		$jobData = vProvisionJobData::getInstance($dbEntry->getSource());
		if ($jobData)
		{
			/* @var $data vProvisionJobData */
			$jobData->populateFromPartner($dbEntry->getPartner());
			$jobData->populateFromEntry($dbEntry);
			vJobsManager::addProvisionProvideJob(null, $dbEntry, $jobData);
		}
		else
		{
			$dbEntry->setStatus(entryStatus::READY);
			$dbEntry->save();
		
			$liveAssets = assetPeer::retrieveByEntryId($dbEntry->getId(),array(assetType::LIVE));
			foreach ($liveAssets as $liveAsset){
				/* @var $liveAsset liveAsset */
				$liveAsset->setStatus(asset::ASSET_STATUS_READY);
				$liveAsset->save();
			}
		}
		
		myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_ENTRY_ADD, $dbEntry, $this->getPartnerId(), null, null, null, $dbEntry->getId());

		$liveStreamEntry->fromObject($dbEntry, $this->getResponseProfile());
		return $liveStreamEntry;
	}

	protected function prepareEntryForInsert(VidiunBaseEntry $entry, entry $dbEntry = null)
	{
		$dbEntry = parent::prepareEntryForInsert($entry, $dbEntry);
		/* @var $dbEntry LiveStreamEntry */
				
		if(in_array($entry->sourceType, array(VidiunSourceType::LIVE_STREAM, VidiunSourceType::LIVE_STREAM_ONTEXTDATA_CAPTIONS)))
		{
			if(!$entry->conversionProfileId)
			{
				$partner = $dbEntry->getPartner();
				if($partner)
					$dbEntry->setConversionProfileId($partner->getDefaultLiveConversionProfileId());
			}
		}
		
		return $dbEntry;
	}
	
	protected function getTemplateEntry($conversionProfileId, $templateEntryId)
	{
		if(!$templateEntryId && $conversionProfileId)
		{
			$conversionProfile = conversionProfile2Peer::retrieveByPk($conversionProfileId);
			if($conversionProfile)
				$templateEntryId = $conversionProfile->getDefaultEntryId();
				
		}
		if($templateEntryId)
		{
			$templateEntry = entryPeer::retrieveByPKNoFilter($templateEntryId, null, false);
			return $templateEntry;
		}
		
		return null;
	}
	
	/**
	 * Get live stream entry by ID.
	 * 
	 * @action get
	 * @param string $entryId Live stream entry id
	 * @param int $version Desired version of the data
	 * @return VidiunLiveStreamEntry The requested live stream entry
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	function getAction($entryId, $version = -1)
	{
		return $this->getEntry($entryId, $version, VidiunEntryType::LIVE_STREAM);
	}
	
	/**
	 * Authenticate live-stream entry against stream token and partner limitations
	 * 
	 * @action authenticate
	 * @param string $entryId Live stream entry id
	 * @param string $token Live stream broadcasting token
	 * @param string $hostname Media server host name
	 * @param VidiunEntryServerNodeType $mediaServerIndex Media server index primary / secondary
	 * @param string $applicationName the application to which entry is being broadcast
	 * @return VidiunLiveStreamEntry The authenticated live stream entry
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::LIVE_STREAM_INVALID_TOKEN
	 */
	function authenticateAction($entryId, $token, $hostname = null, $mediaServerIndex = null, $applicationName = null)
	{
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if (!$dbEntry || $dbEntry->getType() != entryType::LIVE_STREAM)
			throw new VidiunAPIException(VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);
		
		/* @var $dbEntry LiveStreamEntry */
		if ($dbEntry->getStreamPassword() != $token)
			throw new VidiunAPIException(VidiunErrors::LIVE_STREAM_INVALID_TOKEN, $entryId);

		/*
		Patch for autenticate error while performing an immidiate stop/start. Checkup for duplicate streams moved to
		media-server for the moment. 
		if($dbEntry->isStreamAlreadyBroadcasting())
			throw new VidiunAPIException(VidiunErrors::LIVE_STREAM_ALREADY_BROADCASTING, $entryId, $mediaServer->getHostname());
		*/
		
		if($hostname && isset($mediaServerIndex))
			$this->setMediaServerWrapper($dbEntry, $mediaServerIndex, $hostname, VidiunEntryServerNodeStatus::AUTHENTICATED, $applicationName);
		
		$this->validateMaxStreamsNotReached($dbEntry);
		
		$entry = VidiunEntryFactory::getInstanceByType($dbEntry->getType());
		$entry->fromObject($dbEntry, $this->getResponseProfile());
		return $entry;
	}
	
	private function validateMaxStreamsNotReached(LiveEntry $liveEntry)
	{
		//Fetch all entries currently being streamed by partner
		$liveEntries = $this->getLiveEntriesForPartner($liveEntry);
		
		$maxPassthroughStreams = $this->getPartner()->getMaxLiveStreamInputs();
		VidiunLog::debug("Max Passthrough streams [$maxPassthroughStreams]");
		
		$maxTranscodedStreams = 0;
		if(PermissionPeer::isValidForPartner(PermissionName::FEATURE_VIDIUN_LIVE_STREAM_TRANSCODE, $this->getPartnerId()))
		{
			$maxTranscodedStreams = $this->getPartner()->getMaxLiveStreamOutputs();
		}
		VidiunLog::debug("Max transcoded streams [$maxTranscodedStreams]");
		
		$entryConversionProfiles = array();
		$entryConversionProfiles[$liveEntry->getConversionProfileId()][] = $liveEntry->getId();
		foreach($liveEntries as $entry)
		{
			/* @var $entry LiveEntry */
			$entryConversionProfiles[$entry->getConversionProfileId()][] = $entry->getId();
		}
		
		$passthroughEntriesCount = 0;
		$transcodedEntriesCount = 0;
		foreach($entryConversionProfiles as $conversionProfileId => $entriesArray)
		{
			$isCloud = false;
			$flavorParamsConversionProfile = flavorParamsConversionProfilePeer::retrieveByConversionProfile($conversionProfileId);
			foreach($flavorParamsConversionProfile as $flavorParamConversionProfile)
			{
				/* @var $flavorParamConversionProfile flavorParamsConversionProfile */
				if($flavorParamConversionProfile->getOrigin() == VidiunAssetParamsOrigin::CONVERT)
				{
					$isCloud = true;
					break;
				}
			}
			
			if($isCloud)
				$transcodedEntriesCount += count($entriesArray);
			else
				$passthroughEntriesCount += count($entriesArray);
		}
		
		VidiunLog::debug("Live transcoded entries [$transcodedEntriesCount], max live transcoded streams [$maxTranscodedStreams]");
		if($transcodedEntriesCount > $maxTranscodedStreams)
			throw new VidiunAPIException(VidiunErrors::LIVE_STREAM_EXCEEDED_MAX_TRANSCODED, $liveEntry->getId());
		
		VidiunLog::debug("Live Passthrough entries [$passthroughEntriesCount], max live Passthrough streams [$maxPassthroughStreams]");
		if($passthroughEntriesCount > $maxPassthroughStreams)
			throw new VidiunAPIException(VidiunErrors::LIVE_STREAM_EXCEEDED_MAX_PASSTHRU, $liveEntry->getId());
	}
	
	private function getLiveEntriesForPartner(LiveEntry $liveEntry)
	{
		//Fetch all entries currently being streamed by partner
		$connectedEntryServerNodes  = EntryServerNodePeer::retrieveConnectedEntryServerNodesByPartner($liveEntry->getPartnerId(), $liveEntry->getId());
		
		if(!count($connectedEntryServerNodes))
			return array();
		
		$connectedLiveEntryIds = array();
		foreach($connectedEntryServerNodes as $connectedEntryServerNode)
			$connectedLiveEntryIds[] = $connectedEntryServerNode->getEntryId();
		
		return entryPeer::retrieveByPKs($connectedLiveEntryIds);
	}

	/**
	 * Update live stream entry. Only the properties that were set will be updated.
	 * 
	 * @action update
	 * @param string $entryId Live stream entry id to update
	 * @param VidiunLiveStreamEntry $liveStreamEntry Live stream entry metadata to update
	 * @return VidiunLiveStreamEntry The updated live stream entry
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser entry entryId edit
	 */
	function updateAction($entryId, VidiunLiveStreamEntry $liveStreamEntry)
	{
		$this->dumpApiRequest($entryId, true);
		return $this->updateEntry($entryId, $liveStreamEntry, VidiunEntryType::LIVE_STREAM);
	}

	/**
	 * Delete a live stream entry.
	 *
	 * @action delete
	 * @param string $entryId Live stream entry id to delete
	 * 
 	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
 	 * @validateUser entry entryId edit
	 */
	function deleteAction($entryId)
	{
		$this->deleteEntry($entryId, VidiunEntryType::LIVE_STREAM);
	}
	
	/**
	 * List live stream entries by filter with paging support.
	 * 
	 * @action list
     * @param VidiunLiveStreamEntryFilter $filter live stream entry filter
	 * @param VidiunFilterPager $pager Pager
	 * @return VidiunLiveStreamListResponse Wrapper for array of live stream entries and total count
	 */
	function listAction(VidiunLiveStreamEntryFilter $filter = null, VidiunFilterPager $pager = null)
	{
	    if (!$filter)
			$filter = new VidiunLiveStreamEntryFilter();
			
	    $filter->typeEqual = VidiunEntryType::LIVE_STREAM;
	    list($list, $totalCount) = parent::listEntriesByFilter($filter, $pager);
	    
	    $newList = VidiunLiveStreamEntryArray::fromDbArray($list, $this->getResponseProfile());
		$response = new VidiunLiveStreamListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		return $response;
	}
	


	/**
	 * Update live stream entry thumbnail using a raw jpeg file
	 * 
	 * @action updateOfflineThumbnailJpeg
	 * @param string $entryId live stream entry id
	 * @param file $fileData Jpeg file data
	 * @return VidiunLiveStreamEntry The live stream entry
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY
	 */
	function updateOfflineThumbnailJpegAction($entryId, $fileData)
	{
		return parent::updateThumbnailJpegForEntry($entryId, $fileData, VidiunEntryType::LIVE_STREAM, entry::FILE_SYNC_ENTRY_SUB_TYPE_OFFLINE_THUMB);
	}
	
	/**
	 * Update entry thumbnail using url
	 * 
	 * @action updateOfflineThumbnailFromUrl
	 * @param string $entryId live stream entry id
	 * @param string $url file url
	 * @return VidiunLiveStreamEntry The live stream entry
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @throws VidiunErrors::PERMISSION_DENIED_TO_UPDATE_ENTRY
	 */
	function updateOfflineThumbnailFromUrlAction($entryId, $url)
	{
		return parent::updateThumbnailForEntryFromUrl($entryId, $url, VidiunEntryType::LIVE_STREAM, entry::FILE_SYNC_ENTRY_SUB_TYPE_OFFLINE_THUMB);
	}
	
	/**
	 * Delivering the status of a live stream (on-air/offline) if it is possible
	 * 
	 * @action isLive
	 * @param string $id ID of the live stream
	 * @param VidiunPlaybackProtocol $protocol protocol of the stream to test.
	 * @return bool
	 * @vsOptional
	 * 
	 * @throws VidiunErrors::LIVE_STREAM_STATUS_CANNOT_BE_DETERMINED
	 * @throws VidiunErrors::INVALID_ENTRY_ID
	 */
	public function isLiveAction ($id, $protocol)
	{
		if (!vCurrentContext::$vs)
		{
			vEntitlementUtils::initEntitlementEnforcement(null, false);
			$liveStreamEntry = vCurrentContext::initPartnerByEntryId($id);
			if (!$liveStreamEntry || $liveStreamEntry->getStatus() == entryStatus::DELETED)
				throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_ID, $id);

			// enforce entitlement
			$this->setPartnerFilters(vCurrentContext::getCurrentPartnerId());
		}
		else
		{
			$liveStreamEntry = entryPeer::retrieveByPK($id);
		}
		
		if (!$liveStreamEntry || ($liveStreamEntry->getType() != entryType::LIVE_STREAM))
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_ID, $id);

		if (!in_array($liveStreamEntry->getSource(), LiveEntry::$vidiunLiveSourceTypes))
			VidiunResponseCacher::setConditionalCacheExpiry(self::ISLIVE_ACTION_NON_VIDIUN_LIVE_CONDITIONAL_CACHE_EXPIRY);

		/* @var $liveStreamEntry LiveStreamEntry */
	
		if(in_array($liveStreamEntry->getSource(), array(VidiunSourceType::LIVE_STREAM, VidiunSourceType::LIVE_STREAM_ONTEXTDATA_CAPTIONS)))
		{
			return $this->responseHandlingIsLive($liveStreamEntry->isCurrentlyLive());
		}
		
		$dpda= new DeliveryProfileDynamicAttributes();
		$dpda->setEntryId($id);
		$dpda->setFormat($protocol);
		
		switch ($protocol)
		{
			case VidiunPlaybackProtocol::HLS:
			case VidiunPlaybackProtocol::APPLE_HTTP:
				$url = $liveStreamEntry->getHlsStreamUrl('http');
				if($protocol == VidiunPlaybackProtocol::HLS)
					$hlsProtocols = array(VidiunPlaybackProtocol::HLS, VidiunPlaybackProtocol::APPLE_HTTP);
				else
					$hlsProtocols = array(VidiunPlaybackProtocol::APPLE_HTTP, VidiunPlaybackProtocol::HLS);

				foreach ($hlsProtocols as $hlsProtocol){
					$config = $liveStreamEntry->getLiveStreamConfigurationByProtocol($hlsProtocol, requestUtils::getProtocol());
					if ($config){
						$url = $config->getUrl();
						$protocol = $hlsProtocol;
						$dpda->setFormat($protocol);
						break;
					}
				}

				VidiunLog::info('Determining status of live stream URL [' .$url. ']');
				$urlManager = DeliveryProfilePeer::getLiveDeliveryProfileByHostName(parse_url($url, PHP_URL_HOST), $dpda);
				if($urlManager)
					return $this->responseHandlingIsLive($urlManager->isLive($url));

				break;
			case VidiunPlaybackProtocol::HDS:
			case VidiunPlaybackProtocol::AKAMAI_HDS:
				$config = $liveStreamEntry->getLiveStreamConfigurationByProtocol($protocol, requestUtils::getProtocol());
				if ($config)
				{
					$url = $config->getUrl();
					VidiunLog::info('Determining status of live stream URL [' .$url . ']');
					$urlManager = DeliveryProfilePeer::getLiveDeliveryProfileByHostName(parse_url($url, PHP_URL_HOST), $dpda);
					if($urlManager)
						return $this->responseHandlingIsLive($urlManager->isLive($url));
				}
				break;
		}
		
		throw new VidiunAPIException(VidiunErrors::LIVE_STREAM_STATUS_CANNOT_BE_DETERMINED, $protocol);
	}

	private function responseHandlingIsLive($isLive)
	{
		if (!$isLive){
			VidiunResponseCacher::setExpiry(self::ISLIVE_ACTION_CACHE_EXPIRY_WHEN_NOT_LIVE);
			VidiunResponseCacher::setHeadersCacheExpiry(self::ISLIVE_ACTION_CACHE_EXPIRY_WHEN_NOT_LIVE);
		} else {
			VidiunResponseCacher::setExpiry(self::ISLIVE_ACTION_CACHE_EXPIRY_WHEN_LIVE);
			VidiunResponseCacher::setHeadersCacheExpiry(self::ISLIVE_ACTION_CACHE_EXPIRY_WHEN_LIVE);
		}

		return $isLive;
	}


	/**
	 * Add new pushPublish configuration to entry
	 * 
	 * @action addLiveStreamPushPublishConfiguration
	 * @param string $entryId
	 * @param VidiunPlaybackProtocol $protocol
	 * @param string $url
	 * @param VidiunLiveStreamConfiguration $liveStreamConfiguration
	 * @return VidiunLiveStreamEntry
	 * @throws VidiunErrors::INVALID_ENTRY_ID
	 */
	public function addLiveStreamPushPublishConfigurationAction ($entryId, $protocol, $url = null, VidiunLiveStreamConfiguration $liveStreamConfiguration = null)
	{
		$this->dumpApiRequest($entryId);
		
		$entry = entryPeer::retrieveByPK($entryId);
		if (!$entry || $entry->getType() != entryType::LIVE_STREAM)
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_ID);
		
		//Should not allow usage of both $url and $liveStreamConfiguration
		if ($url && !is_null($liveStreamConfiguration))
			throw new VidiunAPIException(VidiunErrors::SERVICE_FORBIDDEN);
			
		/* @var $entry LiveEntry */
		$pushPublishConfigurations = $entry->getPushPublishPlaybackConfigurations();

		$configuration = null;
		if ($url)
		{
			$configuration = new vLiveStreamConfiguration();
			$configuration->setProtocol($protocol);
			$configuration->setUrl($url);
		}
		elseif (!is_null($liveStreamConfiguration))
		{
			$configuration = $liveStreamConfiguration->toInsertableObject();
			$configuration->setProtocol($protocol);
		}
		
		if ($configuration)
		{
			$pushPublishConfigurations[] = $configuration;
			$entry->setPushPublishPlaybackConfigurations($pushPublishConfigurations);
			$entry->save();
		}
		
		$apiEntry = VidiunEntryFactory::getInstanceByType($entry->getType());
		$apiEntry->fromObject($entry, $this->getResponseProfile());
		return $apiEntry;
	}
	
	/**
	 *Remove push publish configuration from entry
	 * 
	 * @action removeLiveStreamPushPublishConfiguration
	 * @param string $entryId
	 * @param VidiunPlaybackProtocol $protocol
	 * @return VidiunLiveStreamEntry
	 * @throws VidiunErrors::INVALID_ENTRY_ID
	 */
	public function removeLiveStreamPushPublishConfigurationAction ($entryId, $protocol)
	{
		$this->dumpApiRequest($entryId);
		
		$entry = entryPeer::retrieveByPK($entryId);
		if (!$entry || $entry->getType() != entryType::LIVE_STREAM)
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_ID);
		
		/* @var $entry LiveEntry */
		$pushPublishConfigurations = $entry->getPushPublishPlaybackConfigurations();
		foreach ($pushPublishConfigurations as $index => $config)
		{
			/* @var $config vLiveStreamConfiguration */
			if ($config->getProtocol() == $protocol)
			{
				unset ($pushPublishConfigurations[$index]);
			}
		}

		$entry->setPushPublishPlaybackConfigurations($pushPublishConfigurations);
		$entry->save();
		
		$apiEntry = VidiunEntryFactory::getInstanceByType($entry->getType());
		$apiEntry->fromObject($entry, $this->getResponseProfile());
		return $apiEntry;
	}
	
	/**
	 * Regenerate new secure token for liveStream
	 * 
	 * @action regenerateStreamToken
	 * @param string $entryId Live stream entry id to regenerate secure token for
	 * @return VidiunLiveEntry The regenerate token entry 
	 * 
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 * @validateUser entry entryId edit
	 */
	public function regenerateStreamTokenAction($entryId)
	{
		
		$this->dumpApiRequest($entryId, true);
	
		$liveEntry = entryPeer::retrieveByPK($entryId);
		if (!$liveEntry || $liveEntry->getType() != entryType::LIVE_STREAM)
			throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_ID);
		
		if (!in_array($liveEntry->getSourceType(), LiveEntry::$vidiunLiveSourceTypes))
			throw new VidiunAPIException(VidiunErrors::CANNOT_REGENERATE_STREAM_TOKEN_FOR_EXTERNAL_LIVE_STREAMS, $liveEntry->getSourceType());
		
		$password = sha1(md5(uniqid(rand(), true)));
		$password = substr($password, rand(0, strlen($password) - 8), 8);
		$liveEntry->setStreamPassword($password);

		$broadcastUrlManager = vBroadcastUrlManager::getInstance($liveEntry->getPartnerId());
		$broadcastUrlManager->setEntryBroadcastingUrls($liveEntry);

		$liveEntry->save();

		$entry = VidiunEntryFactory::getInstanceByType($liveEntry->getType());
		$entry->fromObject($liveEntry, $this->getResponseProfile());
		return $entry;
	}
}
