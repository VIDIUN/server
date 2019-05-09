<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunMediaEntryFilter extends VidiunMediaEntryBaseFilter
{
	static private $map_between_objects = array
	(
		"sourceTypeEqual" => "_eq_source",
		"sourceTypeNotEqual" => "_not_source",
		"sourceTypeIn" => "_in_source",
		"sourceTypeNotIn" => "_notin_source",
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
		
	    $newList = VidiunMediaEntryArray::fromDbArray($list, $responseProfile);
		$response = new VidiunBaseEntryListResponse();
		$response->objects = $newList;
		$response->totalCount = $totalCount;
		
		return $response;
	}
	
	public function __construct()
	{
		$typeArray = array (entryType::MEDIA_CLIP, entryType::LIVE_STREAM);
		$typeArray = array_merge($typeArray, VidiunPluginManager::getExtendedTypes(entryPeer::OM_CLASS, entryType::MEDIA_CLIP));
		$typeArray = array_merge($typeArray, VidiunPluginManager::getExtendedTypes(entryPeer::OM_CLASS, entryType::LIVE_STREAM));
		
		$this->typeIn = implode(',', array_unique($typeArray));
	}
}
