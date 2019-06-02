<?php
/**
 * DoubleClick Service
 *
 * @service doubleClick
 * @package plugins.doubleClickDistribution
 * @subpackage api.services
 */
class DoubleClickService extends ContentDistributionServiceBase
{
	/**
	 * @action getFeed
	 * @disableTags TAG_WIDGET_SESSION,TAG_ENTITLEMENT_ENTRY,TAG_ENTITLEMENT_CATEGORY
	 * @param int $distributionProfileId
	 * @param string $hash
	 * @param int $page
	 * @param int $period
	 * @param string $state
	 * @param bool $ignoreScheduling
	 * @param int $version
	 * @return file
	 * @vsOptional
	 */
	public function getFeedAction($distributionProfileId, $hash, $page = 1, $period = -1, $state = '', $ignoreScheduling = false, $version = 2)
	{
		if (vConf::hasParam('dfp_version_1_partners') && in_array($this->getPartnerId(), vConf::get('dfp_version_1_partners')))
			$version = 1;
		$context = new DoubleClickServiceContext($hash, $page, $period, $state, $ignoreScheduling, $version);
		$context->keepScheduling = !$ignoreScheduling;
		return $this->generateFeed($context, $distributionProfileId, $hash);
	}
	
	public function getProfileClass() {
		return new DoubleClickDistributionProfile();
	}
	
	protected function fillStateDependentFields($context) {
	
		if ($context->state)
		{
			$stateDecoded = base64_decode($context->state);
			if (strpos($stateDecoded, '|') !== false)
			{
				$stateExploded = explode('|', $stateDecoded);
				$context->stateLastEntryTimeMark = $stateExploded[0];
				$stateLastEntryIdsStr =  $stateExploded[1];
				$context->stateLastEntryIds = explode(',', $stateLastEntryIdsStr);
			}
		}
	}
	protected function fillnextStateDependentFields ($context, $entries) {
		// Find the new state
		$context->nextPageStateLastEntryTimeMark = $context->stateLastEntryTimeMark;
		$context->nextPageStateLastEntryIds = $context->stateLastEntryIds;
		foreach($entries as $entry)
		{
			if($context->version < 2)
				$timeMark = $entry->getCreatedAt(null);
			else
				$timeMark = $entry->getUpdatedAt(null);

			if ($context->nextPageStateLastEntryTimeMark > $timeMark)
				$context->nextPageStateLastEntryIds = array();

			$context->nextPageStateLastEntryIds[] = $entry->getId();
			$context->nextPageStateLastEntryTimeMark = $timeMark;
		}
	}
	
	protected function getEntryFilter($context, $keepScheduling = true)
	{
		$keepScheduling = ($keepScheduling === true && $this->profile->getIgnoreSchedulingInFeed() !== true);
		$entryFilter = parent::getEntryFilter($context, $keepScheduling);
		$entryFilter->set('_order_by', '-created_at');
		if ($context->period && $context->period > 0)
			$entryFilter->set('_gte_updated_at', time() - 24*60*60); // last 24 hours
		
		
		// Dummy query to get the total count
		$baseCriteria = VidiunCriteria::create(entryPeer::OM_CLASS);
		$baseCriteria->add(entryPeer::DISPLAY_IN_SEARCH, mySearchUtils::DISPLAY_IN_SEARCH_SYSTEM, Criteria::NOT_EQUAL);
		$baseCriteria->setLimit(1);
		$entryFilter->attachToCriteria($baseCriteria);
		$entries = entryPeer::doSelect($baseCriteria);
		$context->totalCount = $baseCriteria->getRecordsCount();
		
		// Add the state data to proceed to next page
		$this->fillStateDependentFields($context);
		
		if ($context->stateLastEntryTimeMark)
		{
			if ($context->version < 2)
				$entryFilter->set('_lte_created_at', $context->stateLastEntryTimeMark);
			else
				$entryFilter->set('_lte_updated_at', $context->stateLastEntryTimeMark);
		}

		if ($context->stateLastEntryIds)
			$entryFilter->set('_notin_id', $context->stateLastEntryIds);
		
		return $entryFilter;
	}
	
	protected function getEntries($context, $orderBy = null, $limit = null) {
		$context->hasNextPage = false;
		$orderBy = null;
		if($context->version == 2)
			$orderBy = entryPeer::UPDATED_AT;
		$entries = parent::getEntries($context, $orderBy, $this->profile->getItemsPerPage() + 1); // get +1 to check if we have next page
		if (count($entries) === ($this->profile->getItemsPerPage() + 1)) { // we tried to get (itemsPerPage + 1) entries, meaning we have another page
			$context->hasNextPage = true;
			unset($entries[$this->profile->getItemsPerPage()]);
		}
		
		$this->fillnextStateDependentFields($context, $entries);
		return $entries;
	}
	
	protected function createFeedGenerator($context) 
	{
		// Construct the feed
		$distributionProfileId = $this->profile->getId();

		$templateName = 'doubleclick_template.xml';
		if($context->version == 2)
			$templateName = 'doubleclick_version2_template.xml';

		$feed = new DoubleClickFeed($templateName, $this->profile, $context->version);

		if($context->version < 2)
		{
			$feed->setTotalResult($context->totalCount);
			$feed->setStartIndex(($context->page - 1) * $this->profile->getItemsPerPage() + 1);
		}

		$feed->setSelfLink($this->getUrl($distributionProfileId, $context->hash, $context->page, $context->period, $context->stateLastEntryTimeMark, $context->stateLastEntryIds, $context->version));
		if ($context->hasNextPage)
			$feed->setNextLink($this->getUrl($distributionProfileId, $context->hash, $context->page + 1, $context->period, $context->nextPageStateLastEntryTimeMark, $context->nextPageStateLastEntryIds, $context->version));
		
		return $feed;
	}
	
	protected function handleEntry($context, $feed,entry $entry, Entrydistribution $entryDistribution)
	{
		$fields = $this->profile->getAllFieldValues($entryDistribution);
		$flavorAssets = assetPeer::retrieveByIds(explode(',', $entryDistribution->getFlavorAssetIds()));
		$thumbAssets = assetPeer::retrieveByIds(explode(',', $entryDistribution->getThumbAssetIds()));
		
		$cuePoints = $this->getCuePoints($entry->getPartnerId(), $entry->getId());

		$captionAssets = null;
		if($context->version == 2)
			$captionAssets = assetPeer::retrieveByEntryId($entry->getId(), array(CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION)));
		return $feed->getItemXml($fields, $flavorAssets, $thumbAssets, $cuePoints, $captionAssets, $entry);
	}
	
	/**
	 * @action getFeedByEntryId
	 * @disableTags TAG_WIDGET_SESSION,TAG_ENTITLEMENT_ENTRY,TAG_ENTITLEMENT_CATEGORY
	 * @param int $distributionProfileId
	 * @param string $hash
	 * @param string $entryId
	 * @param int $version
	 * @return file
	 * @vsOptional
	 */
	public function getFeedByEntryIdAction($distributionProfileId, $hash, $entryId, $version = 2)
	{
		$this->validateRequest($distributionProfileId, $hash);

		// Creates entry filter with advanced filter
		$entry = entryPeer::retrieveByPK($entryId);
		if (!$entry || ($entry->getPartnerId() != $this->getPartnerId()))
			throw new VidiunAPIException (VidiunErrors::ENTRY_ID_NOT_FOUND, $entryId);

		// Construct the feed
		if (vConf::hasParam('dfp_version_1_partners') && in_array($this->getPartnerId(), vConf::get('dfp_version_1_partners')))
			$version = 1;

		$templateName = 'doubleclick_template.xml';
		if($version == 2)
			$templateName = 'doubleclick_version2_template.xml';

		$feed = new DoubleClickFeed ($templateName, $this->profile, $version);
		if($version == 2)
		{
			$feed->setTotalResult(1);
			$feed->setStartIndex(1);
		}
		
		$entries = array();
		$entries[] = $entry;
		$context = new DoubleClickServiceContext($hash, 1, -1, '', false, $version);
		$this->handleEntries($context, $feed, $entries);
		return $this->doneFeedGeneration($context, $feed);
		
	}
	
	/**
	 * @param $entryId
	 */
	protected function getCuePoints($partnerId, $entryId)
	{
		$c = VidiunCriteria::create(CuePointPeer::OM_CLASS);
		$c->add(CuePointPeer::PARTNER_ID, $partnerId);
		$c->add(CuePointPeer::ENTRY_ID, $entryId);
		$c->add(CuePointPeer::TYPE, AdCuePointPlugin::getCuePointTypeCoreValue(AdCuePointType::AD));
		$c->addAscendingOrderByColumn(CuePointPeer::START_TIME);
		return CuePointPeer::doSelect($c);
	}
	
	/**
	 * @param int $distributionProfileId
	 * @param string $hash
	 * @param int $page
	 */
	protected function getUrl($distributionProfileId, $hash, $page, $period, $stateLastEntryTimeMark, $stateLastEntryIds, $version)
	{
		if (!is_null($stateLastEntryTimeMark) && !is_null($stateLastEntryIds) && count($stateLastEntryIds) > 0)
			$state = $stateLastEntryTimeMark.'|'.implode(',', $stateLastEntryIds);
		else
			$state = '';
		$urlParams = array(
			'service' => 'doubleclickdistribution_doubleclick',
			'action' => 'getFeed',
			'partnerId' => $this->getPartnerId(),
			'distributionProfileId' => $distributionProfileId,
			'hash' => $hash,
			'version' => $version,
			'page' => $page,
			'state' => base64_encode($state),
			'period' => $period,
		);
		return requestUtils::getRequestHost() . '/api_v3/index.php?' . http_build_query($urlParams, null, '&');	
	}
}
