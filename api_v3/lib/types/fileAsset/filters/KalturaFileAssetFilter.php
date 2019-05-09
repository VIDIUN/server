<?php
/**
 * @package api
 * @subpackage api.filters
 */
class VidiunFileAssetFilter extends VidiunFileAssetBaseFilter
{
	static private $map_between_objects = array
	(
		"fileAssetObjectTypeEqual" => "_eq_object_type",
	);

	/* (non-PHPdoc)
	 * @see VidiunFileAssetBaseFilter::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new fileAssetFilter();
	}
	
	/* (non-PHPdoc)
	 * @see VidiunFilter::toObject()
	 */
	public function toObject ( $object_to_fill = null, $props_to_skip = array() )
	{
		$this->validatePropertyNotNull('fileAssetObjectTypeEqual');
		$this->validatePropertyNotNull(array('objectIdEqual', 'objectIdIn', 'idIn', 'idEqual'));
		return parent::toObject($object_to_fill, $props_to_skip);
	}

	/* (non-PHPdoc)
	 * @see VidiunRelatedFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$fileAssetFilter = $this->toObject();

		$c = new Criteria();
		$fileAssetFilter->attachToCriteria($c);
		
		$totalCount = FileAssetPeer::doCount($c);
		
		$pager->attachToCriteria($c);
		$dbList = FileAssetPeer::doSelect($c);
		
		$response = new VidiunFileAssetListResponse();
		$response->objects = VidiunFileAssetArray::fromDbArray($dbList, $responseProfile);
		$response->totalCount = $totalCount;
		return $response; 
	}
}
