<?php
/**
 * Add & Manage Syndication Feeds
 *
 * @service syndicationFeed
 * @package api
 * @subpackage services
 */
class SyndicationFeedService extends VidiunBaseService 
{
	
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('asset');
		$this->applyPartnerFilterForClass('assetParams');
		$this->applyPartnerFilterForClass('assetParamsOutput');
		$this->applyPartnerFilterForClass('entry');
		$this->applyPartnerFilterForClass('syndicationFeed');
	}
	
	protected function partnerGroup($peer = null)
	{
		// required in order to load flavor params of partner zero
		if ($this->actionName == 'requestConversion')
			return parent::partnerGroup() . ',0';

		return parent::partnerGroup();
	}
	
	protected function vidiunNetworkAllowed($actionName)
	{
		if ($actionName === 'get') {
			return true;
		}
		if ($actionName === 'list') {
			return true;
		}

		return parent::vidiunNetworkAllowed($actionName);
	}
	
	/**
	 * Add new Syndication Feed
	 * 
	 * @action add
	 * @param VidiunBaseSyndicationFeed $syndicationFeed
	 * @return VidiunBaseSyndicationFeed
	 *
	 * @disableRelativeTime $syndicationFeed
	 */
	public function addAction(VidiunBaseSyndicationFeed $syndicationFeed)
	{
		$syndicationFeed->validatePlaylistId();
		$syndicationFeed->validateStorageId($this->getPartnerId());

		$propertiesToValidate = $syndicationFeed->getPropertiesToValidate();
		foreach ($propertiesToValidate as $propName => $propValue)
		{
			$syndicationFeed->validatePropertyNotNull($propName);
		}
			
		$syndicationFeedDB = $syndicationFeed->toInsertableObject();
		$syndicationFeedDB->setPartnerId($this->getPartnerId());
		$syndicationFeedDB->setStatus(VidiunSyndicationFeedStatus::ACTIVE);
		$syndicationFeedDB->save();
		
		if($syndicationFeed->addToDefaultConversionProfile)
		{
			
			$partner = PartnerPeer::retrieveByPK($this->getPartnerId());
		
			$c = new Criteria();
			$c->addAnd(flavorParamsConversionProfilePeer::CONVERSION_PROFILE_ID, $partner->getDefaultConversionProfileId());
			$c->addAnd(flavorParamsConversionProfilePeer::FLAVOR_PARAMS_ID, $syndicationFeed->flavorParamId);
			$is_exist = flavorParamsConversionProfilePeer::doCount($c);
			if(!$is_exist || $is_exist === 0)
			{
				$assetParams = assetParamsPeer::retrieveByPK($syndicationFeed->flavorParamId);
				
				$fpc = new flavorParamsConversionProfile();
				$fpc->setConversionProfileId($partner->getDefaultConversionProfileId());
				$fpc->setFlavorParamsId($syndicationFeed->flavorParamId);
				
				if($assetParams)
				{
					$fpc->setReadyBehavior($assetParams->getReadyBehavior());
					$fpc->setSystemName($assetParams->getSystemName());
					
					if($assetParams->hasTag(assetParams::TAG_SOURCE) || $assetParams->hasTag(assetParams::TAG_INGEST))
						$fpc->setOrigin(assetParamsOrigin::INGEST);
					else
						$fpc->setOrigin(assetParamsOrigin::CONVERT);
				}
				
				
				$fpc->save();
			}
		}
		
		if ($syndicationFeed instanceof VidiunGenericXsltSyndicationFeed ){
			$key = $syndicationFeedDB->getSyncKey(genericSyndicationFeed::FILE_SYNC_SYNDICATION_FEED_XSLT);
			vFileSyncUtils::file_put_contents($key, $syndicationFeed->xslt);
		}
		
		$syndicationFeed->fromObject($syndicationFeedDB, $this->getResponseProfile());
	
		return $syndicationFeed;
	}
	
	/**
	 * Get Syndication Feed by ID
	 * 
	 * @action get
	 * @param string $id
	 * @return VidiunBaseSyndicationFeed
	 * @throws VidiunErrors::INVALID_FEED_ID
	 */
	public function getAction($id)
	{
		$syndicationFeedDB = syndicationFeedPeer::retrieveByPK($id);
		if (!$syndicationFeedDB)
			throw new VidiunAPIException(VidiunErrors::INVALID_FEED_ID, $id);
			
		$syndicationFeed = VidiunSyndicationFeedFactory::getInstanceByType($syndicationFeedDB->getType());
		//echo $syndicationFeed->feedUrl; die;
		$syndicationFeed->fromObject($syndicationFeedDB, $this->getResponseProfile());
		return $syndicationFeed;
	}
        
	/**
	 * Update Syndication Feed by ID
	 * 
	 * @action update
	 * @param string $id
	 * @param VidiunBaseSyndicationFeed $syndicationFeed
	 * @return VidiunBaseSyndicationFeed
	 * @throws VidiunErrors::INVALID_FEED_ID
	 *
	 * @disableRelativeTime $syndicationFeed
	 */
	public function updateAction($id, VidiunBaseSyndicationFeed $syndicationFeed)
	{
		$syndicationFeedDB = syndicationFeedPeer::retrieveByPK($id);
		if (!$syndicationFeedDB)
			throw new VidiunAPIException(VidiunErrors::INVALID_FEED_ID, $id);
		
		$syndicationFeed->validateStorageId($this->getPartnerId());
		$syndicationFeed->toUpdatableObject($syndicationFeedDB, array('type'));	
		
		if (($syndicationFeed instanceof VidiunGenericXsltSyndicationFeed) && ($syndicationFeed->xslt != null)){
			if(!($syndicationFeedDB instanceof genericSyndicationFeed))
				throw new VidiunAPIException(VidiunErrors::INVALID_FEED_TYPE, get_class($syndicationFeedDB));
				
			$syndicationFeedDB->incrementVersion();
		}
		$syndicationFeedDB->save();		
		
		
		if (($syndicationFeed instanceof VidiunGenericXsltSyndicationFeed) && ($syndicationFeed->xslt != null)){			
			$key = $syndicationFeedDB->getSyncKey(genericSyndicationFeed::FILE_SYNC_SYNDICATION_FEED_XSLT);
			vFileSyncUtils::file_put_contents($key, $syndicationFeed->xslt);
		}
		
        $syndicationFeed->type = null;
        
		$syndicationFeed = VidiunSyndicationFeedFactory::getInstanceByType($syndicationFeedDB->getType());
		$syndicationFeed->fromObject($syndicationFeedDB, $this->getResponseProfile());
		return $syndicationFeed;
	}
	
	/**
	 * Delete Syndication Feed by ID
	 * 
	 * @action delete
	 * @param string $id
	 * @throws VidiunErrors::INVALID_FEED_ID
	 */
	public function deleteAction($id)
	{
		$syndicationFeedDB = syndicationFeedPeer::retrieveByPK($id);
		if (!$syndicationFeedDB)
			throw new VidiunAPIException(VidiunErrors::INVALID_FEED_ID, $id);
		
		
		$syndicationFeedDB->setStatus(VidiunSyndicationFeedStatus::DELETED);
		$syndicationFeedDB->save();
	}
	
	/**
	 * List Syndication Feeds by filter with paging support
	 * 
	 * @action list
	 * @param VidiunBaseSyndicationFeedFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunBaseSyndicationFeedListResponse
	 */
	public function listAction(VidiunBaseSyndicationFeedFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if ($filter === null)
			$filter = new VidiunBaseSyndicationFeedFilter();
			
		if ($filter->orderBy === null)
			$filter->orderBy = VidiunBaseSyndicationFeedOrderBy::CREATED_AT_DESC;
			
		$syndicationFilter = new syndicationFeedFilter();
		
		$filter->toObject($syndicationFilter);

		$c = new Criteria();
		$syndicationFilter->attachToCriteria($c);
		$c->add(syndicationFeedPeer::DISPLAY_IN_SEARCH, mySearchUtils::DISPLAY_IN_SEARCH_SYSTEM, Criteria::NOT_EQUAL);
		
		$totalCount = syndicationFeedPeer::doCount($c);
                
        if($pager === null)
        	$pager = new VidiunFilterPager();
                
        $pager->attachToCriteria($c);
		$dbList = syndicationFeedPeer::doSelect($c);
		
		$list = VidiunBaseSyndicationFeedArray::fromDbArray($dbList, $this->getResponseProfile());
		$response = new VidiunBaseSyndicationFeedListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response;
		
	}
	
	/**
	 * get entry count for a syndication feed
	 *
	 * @action getEntryCount
	 * @param string $feedId
	 * @return VidiunSyndicationFeedEntryCount
	 * @throws VidiunErrors::INVALID_FEED_ID
	 */
	public function getEntryCountAction($feedId)
	{
		$syndicationFeedDB = syndicationFeedPeer::retrieveByPK($feedId);
		if (!$syndicationFeedDB)
			throw new VidiunAPIException(VidiunErrors::INVALID_FEED_ID, $feedId);
		
		$feedCount = new VidiunSyndicationFeedEntryCount();
		
		try
		{
			$feedRenderer = new VidiunSyndicationFeedRenderer($feedId);
			$feedCount->totalEntryCount = $feedRenderer->getEntriesCount();

			$feedRenderer = new VidiunSyndicationFeedRenderer($feedId);
			$feedRenderer->addFlavorParamsAttachedFilter();
			$feedCount->actualEntryCount = $feedRenderer->getEntriesCount();
		}
		catch (vCoreException $exception)
		{
			$code = $exception->getCode();
			$data = $exception->getData();
			switch ($code)
			{
				case vCoreException::INVALID_ENTRY_ID:
					$id = isset($data['playlistId']) ? $data['playlistId'] : '';
					throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_ID, $id);
				case vCoreException::INVALID_ENTRY_TYPE:
					$id = isset($data['playlistId']) ? $data['playlistId'] : '';
					$wrongType = isset($data['wrongType']) ? $data['wrongType'] : '';
					$correctType = isset($data['correctType']) ? $data['correctType'] : '';
					throw new VidiunAPIException(VidiunErrors::INVALID_ENTRY_TYPE, $id, $wrongType, $correctType);
				default:
					throw $exception;
			}
		}

		$feedCount->requireTranscodingCount = $feedCount->totalEntryCount - $feedCount->actualEntryCount;
		
		return $feedCount;
	}
	
	/**
	 *  request conversion for all entries that doesn't have the required flavor param
	 *  returns a comma-separated ids of conversion jobs
	 *
	 *  @action requestConversion
	 *  @param string $feedId
	 *  @return string
	 * @throws VidiunErrors::INVALID_FEED_ID
	 */
	public function requestConversionAction($feedId)
	{
		$syndicationFeedDB = syndicationFeedPeer::retrieveByPK($feedId);
		if (!$syndicationFeedDB)
			throw new VidiunAPIException(VidiunErrors::INVALID_FEED_ID, $feedId);
			
		// find entry ids that already converted to the flavor
		$feedRendererWithTheFlavor = new VidiunSyndicationFeedRenderer($feedId);
		$feedRendererWithTheFlavor->addFlavorParamsAttachedFilter();
		$entriesWithTheFlavor = $feedRendererWithTheFlavor->getEntriesIds();
		
		// create filter of the entries that not converted
		$entryFilter = new entryFilter();
		$entryFilter->setIdNotIn($entriesWithTheFlavor);
		
		// create feed with the new filter
		$feedRendererToConvert = new VidiunSyndicationFeedRenderer($feedId);
		$feedRendererToConvert->addFilter($entryFilter);
		
		$createdJobsIds = array();
		$flavorParamsId = $feedRendererToConvert->syndicationFeed->flavorParamId;
		
		while($entry = $feedRendererToConvert->getNextEntry())
		{
			$originalFlavorAsset = assetPeer::retrieveOriginalByEntryId($entry->getId());
			if (!is_null($originalFlavorAsset))
			{
				$err = "";
				$job = vBusinessPreConvertDL::decideAddEntryFlavor(null, $entry->getId(), $flavorParamsId, $err);
				if($job && is_object($job))
					$createdJobsIds[] = $job->getId();
			}
		}
		return(implode(',', $createdJobsIds));
	}
}
