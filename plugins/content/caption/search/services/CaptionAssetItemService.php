<?php

/**
 * Search caption asset items
 *
 * @service captionAssetItem
 * @package plugins.captionSearch
 * @subpackage api.services
 */
class CaptionAssetItemService extends VidiunBaseService
{

	const SIZE_OF_ENTRIES_CHUNK = 150;
	const MAX_NUMBER_OF_ENTRIES = 1000;
	
	public function initService($serviceId, $serviceName, $actionName)
	{
		$vs = vCurrentContext::$vs_object ? vCurrentContext::$vs_object : null;
		
		if (($actionName == 'search') &&
		  (!$vs || (!$vs->isAdmin() && !$vs->verifyPrivileges(vs::PRIVILEGE_LIST, vs::PRIVILEGE_WILDCARD))))
		{
			VidiunCriterion::enableTag(VidiunCriterion::TAG_WIDGET_SESSION);
			entryPeer::setUserContentOnly(true);
		}

		parent::initService($serviceId, $serviceName, $actionName);
		
		if($actionName != 'parse')
		{
			$this->applyPartnerFilterForClass('asset');
			$this->applyPartnerFilterForClass('CaptionAssetItem');
		}
		
		if(!CaptionSearchPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, CaptionSearchPlugin::PLUGIN_NAME);
	}
	
    /**
     * Parse content of caption asset and index it
     *
     * @action parse
     * @param string $captionAssetId
     * @throws VidiunCaptionErrors::CAPTION_ASSET_ID_NOT_FOUND
     */
    function parseAction($captionAssetId)
    {
		//do nothing
    }
	
	/**
	 * Search caption asset items by filter, pager and free text
	 *
	 * @action search
	 * @param VidiunBaseEntryFilter $entryFilter
	 * @param VidiunCaptionAssetItemFilter $captionAssetItemFilter
	 * @param VidiunFilterPager $captionAssetItemPager
	 * @return VidiunCaptionAssetItemListResponse
	 */
	function searchAction(VidiunBaseEntryFilter $entryFilter = null, VidiunCaptionAssetItemFilter $captionAssetItemFilter = null, VidiunFilterPager $captionAssetItemPager = null)
	{
		if (!$captionAssetItemPager)
		{
			$captionAssetItemPager = new VidiunFilterPager();
		}

		if (!$captionAssetItemFilter)
		{
			$captionAssetItemFilter = new VidiunCaptionAssetItemFilter();
		}

		$captionAssetItemFilter->validatePropertyNotNull(array("contentLike", "contentMultiLikeOr", "contentMultiLikeAnd"));

		$captionAssetItemCoreFilter = new CaptionAssetItemFilter();
		$captionAssetItemFilter->toObject($captionAssetItemCoreFilter);

		$captionItemQueryToFilter = new ESearchCaptionQueryFromFilter();

		$filterOnEntryIds = false;
		if($entryFilter || vEntitlementUtils::getEntitlementEnforcement())
		{
			$entryCoreFilter = new entryFilter();
			if($entryFilter)
			{
				$entryFilter->toObject($entryCoreFilter);
			}
			$entryCoreFilter->setPartnerSearchScope($this->getPartnerId());
			$this->addEntryAdvancedSearchFilter($captionAssetItemFilter, $entryCoreFilter);

			$entryCriteria = VidiunCriteria::create(entryPeer::OM_CLASS);
			$entryCoreFilter->attachToCriteria($entryCriteria);
			$entryCriteria->applyFilters();

			$entryIds = $entryCriteria->getFetchedIds();
			if(!$entryIds || !count($entryIds))
			{
				$entryIds = array('NOT_EXIST');
			}

			$captionAssetItemCoreFilter->setEntryIdIn($entryIds);
			$filterOnEntryIds = true;
			if($entryCoreFilter->get('_eq_id'))
			{
				$captionItemQueryToFilter->setEntryIdEqual();
			}
		}

		$captionAssetItemCorePager = new vFilterPager();
		$captionAssetItemPager->toObject($captionAssetItemCorePager);
		list($captionAssetItems, $objectsCount) = $captionItemQueryToFilter->retrieveElasticQueryCaptions($captionAssetItemCoreFilter, $captionAssetItemCorePager, $filterOnEntryIds);

		$list = VidiunCaptionAssetItemArray::fromDbArray($captionAssetItems, $this->getResponseProfile());
		$response = new VidiunCaptionAssetItemListResponse();
		$response->objects = $list;
		$response->totalCount = $objectsCount;
		return $response;
	}
	
	private function addEntryAdvancedSearchFilter(VidiunCaptionAssetItemFilter $captionAssetItemFilter, entryFilter $entryCoreFilter)
	{
		//create advanced filter on entry caption
		$entryCaptionAdvancedSearch = new EntryCaptionAssetSearchFilter();
		$entryCaptionAdvancedSearch->setContentLike($captionAssetItemFilter->contentLike);
		$entryCaptionAdvancedSearch->setContentMultiLikeAnd($captionAssetItemFilter->contentMultiLikeAnd);
		$entryCaptionAdvancedSearch->setContentMultiLikeOr($captionAssetItemFilter->contentMultiLikeOr);
		$inputAdvancedSearch = $entryCoreFilter->getAdvancedSearch();
		if(!is_null($inputAdvancedSearch))
		{
			$advancedSearchOp = new AdvancedSearchFilterOperator();
			$advancedSearchOp->setType(AdvancedSearchFilterOperator::SEARCH_AND);
			$advancedSearchOp->setItems(array ($inputAdvancedSearch, $entryCaptionAdvancedSearch));
			$entryCoreFilter->setAdvancedSearch($advancedSearchOp);
		}
		else
		{
			$entryCoreFilter->setAdvancedSearch($entryCaptionAdvancedSearch);
		}
	}
	
	
	/**
	 * Search caption asset items by filter, pager and free text
	 *
	 * @action searchEntries
	 * @param VidiunBaseEntryFilter $entryFilter
	 * @param VidiunCaptionAssetItemFilter $captionAssetItemFilter
	 * @param VidiunFilterPager $captionAssetItemPager
	 * @return VidiunBaseEntryListResponse
	 */
	public function searchEntriesAction (VidiunBaseEntryFilter $entryFilter = null, VidiunCaptionAssetItemFilter $captionAssetItemFilter = null, VidiunFilterPager $captionAssetItemPager = null)
	{
		if (!$captionAssetItemPager)
		{
			$captionAssetItemPager = new VidiunFilterPager();
		}
		if (!$captionAssetItemFilter)
		{
			$captionAssetItemFilter = new VidiunCaptionAssetItemFilter();
		}

		$captionAssetItemFilter->validatePropertyNotNull(array("contentLike", "contentMultiLikeOr", "contentMultiLikeAnd"));

		$captionAssetItemCoreFilter = new CaptionAssetItemFilter();
		$captionAssetItemFilter->toObject($captionAssetItemCoreFilter);

		$entryIdChunks = array(NULL);
		$shouldSortCaptionFiltering = false;

		if($entryFilter || vEntitlementUtils::getEntitlementEnforcement())
		{
			$entryCoreFilter = new entryFilter();
			if($entryFilter)
			{
				$entryFilter->toObject($entryCoreFilter);
			}
			$entryCoreFilter->setPartnerSearchScope($this->getPartnerId());
			$this->addEntryAdvancedSearchFilter($captionAssetItemFilter, $entryCoreFilter);

			$entryCriteria = VidiunCriteria::create(entryPeer::OM_CLASS);
			$entryCoreFilter->attachToCriteria($entryCriteria);
			$entryCriteria->setLimit(self::MAX_NUMBER_OF_ENTRIES);

			$entryCriteria->applyFilters();

			$entryIds = $entryCriteria->getFetchedIds();
			if(!$entryIds || !count($entryIds))
			{
				$entryIds = array('NOT_EXIST');
			}

			$entryIdChunks = array_chunk($entryIds , self::SIZE_OF_ENTRIES_CHUNK);
			$shouldSortCaptionFiltering = $entryFilter->orderBy ? true : false;
		}

		$entries = array();
		$counter = 0;

		$captionAssetItemCorePager = new vPager();
		$captionAssetItemPager->toObject($captionAssetItemCorePager);

		$captionItemQueryToFilter = new ESearchCaptionQueryFromFilter();

		foreach ($entryIdChunks as $chunk)
		{
			$currCoreFilter = clone ($captionAssetItemCoreFilter);
			$currCorePager = clone ($captionAssetItemCorePager);
			if ($chunk)
			{
				$currCoreFilter->setEntryIdIn($chunk);
				$currCorePager->setPageSize(sizeof($chunk));
				$currCorePager->setPageIndex(1);
			}

			list ($currEntries, $count) = $captionItemQueryToFilter->retrieveElasticQueryEntryIds($currCoreFilter, $currCorePager);
			//sorting this chunk according to results of first sphinx query
			if ($shouldSortCaptionFiltering)
			{
				$currEntries = array_intersect($entryIds, $currEntries);
			}
			$entries = array_merge ($entries, $currEntries);
			$counter += $count;
		}

		$inputPageSize = $captionAssetItemPager->pageSize;
		$inputPageIndex = $captionAssetItemPager->pageIndex;

		//page index & size validation - no negative values & size not too big
		$pageSize = max(min($inputPageSize, baseObjectFilter::getMaxInValues()), 0);
		$pageIndex = max($captionAssetItemPager::MIN_PAGE_INDEX, $inputPageIndex) - 1;

		$firstIndex = $pageSize * $pageIndex ;
		$entries = array_slice($entries , $firstIndex , $pageSize);

		$dbList = entryPeer::retrieveByPKs($entries);

		if ($shouldSortCaptionFiltering)
		{
			//results ids mapping
			$entriesMapping = array();
			foreach($dbList as $item)
			{
				$entriesMapping[$item->getId()] = $item;
			}

			$dbList = array();
			foreach($entries as $entryId)
			{
				if (isset($entriesMapping[$entryId]))
				{
					$dbList[] = $entriesMapping[$entryId];
				}
			}
		}
		$list = VidiunBaseEntryArray::fromDbArray($dbList, $this->getResponseProfile());
		$response = new VidiunBaseEntryListResponse();
		$response->objects = $list;
		$response->totalCount = $counter;

		return $response;
	}


	/**
	 * List caption asset items by filter and pager
	 *
	 * @action list
	 * @param string $captionAssetId
	 * @param VidiunCaptionAssetItemFilter $captionAssetItemFilter
	 * @param VidiunFilterPager $captionAssetItemPager
	 * @return VidiunCaptionAssetItemListResponse
	 */
	function listAction($captionAssetId, VidiunCaptionAssetItemFilter $captionAssetItemFilter = null, VidiunFilterPager $captionAssetItemPager = null)
	{

		if (!$captionAssetItemPager)
			$captionAssetItemPager = new VidiunFilterPager();

		if (!$captionAssetItemFilter)
			$captionAssetItemFilter = new VidiunCaptionAssetItemFilter();

		$captionAssetItemCoreFilter = new CaptionAssetItemFilter();
		$captionAssetItemFilter->toObject($captionAssetItemCoreFilter);
	        $captionAssetItemFilter->idEqual = $captionAssetId;

        	$captionAsset = assetPeer::retrieveById($captionAssetId);
	        $entryId = $captionAsset->getEntryId();
	        $entryFilter = new VidiunBaseEntryFilter();
	        $entryFilter->idEqual = $entryId;

        	$response = CaptionAssetItemService::searchAction( $entryFilter , $captionAssetItemFilter , $captionAssetItemPager );
	        return $response;
	}

}
