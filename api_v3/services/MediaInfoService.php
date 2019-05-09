<?php
/**
 * Media Info service
 *
 * @service mediaInfo
 * @package api
 * @subpackage services
 */
class MediaInfoService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('mediaInfo');
		$this->applyPartnerFilterForClass('asset');
    }
	
	/**
	 * List media info objects by filter and pager
	 * 
	 * @action list
	 * @param VidiunMediaInfoFilter $filter
	 * @param VidiunFilterPager $pager
	 * @return VidiunMediaInfoListResponse
	 */
	function listAction(VidiunMediaInfoFilter $filter = null, VidiunFilterPager $pager = null)
	{
	    myDbHelper::$use_alternative_con = myDbHelper::DB_HELPER_CONN_PROPEL2;
	    
		if (!$filter)
			$filter = new VidiunMediaInfoFilter();

		if (!$pager)
			$pager = new VidiunFilterPager();
			
		$mediaInfoFilter = new MediaInfoFilter();
		
		$filter->toObject($mediaInfoFilter);
		
		if ($filter->flavorAssetIdEqual)
		{
			// Since media_info table does not have partner_id column, enforce partner by getting the asset
			if (!assetPeer::retrieveById($filter->flavorAssetIdEqual))
				throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $filter->flavorAssetIdEqual);
		}

		$c = new Criteria();
		$mediaInfoFilter->attachToCriteria($c);
		
		$totalCount = mediaInfoPeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = mediaInfoPeer::doSelect($c);
		
		$list = VidiunMediaInfoArray::fromDbArray($dbList, $this->getResponseProfile());
		$response = new VidiunMediaInfoListResponse();
		$response->objects = $list;
		$response->totalCount = $totalCount;
		return $response;
	}
}
