<?php
/**
 * @package plugins.document
 * @subpackage api.filters
 */
class VidiunDocumentEntryFilter extends VidiunDocumentEntryBaseFilter
{
	static private $map_between_objects = array
	(
		"assetParamsIdsMatchOr" => "_matchor_flavor_params_ids",
		"assetParamsIdsMatchAnd" => "_matchand_flavor_params_ids",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseEntryFilter::getListResponse()
	 */
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		list($list, $totalCount) = $this->doGetListResponse($pager);
		
	    $newList = VidiunDocumentEntryArray::fromDbArray($list, $responseProfile);
		$response = new VidiunBaseEntryListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		
		return $response;
	}
}
