<?php
/**
 * @package plugins.metadata
 * @subpackage api.filters
 */
class VidiunMetadataProfileFilter extends VidiunMetadataProfileBaseFilter
{
	static private $map_between_objects = array
	(
		"metadataObjectTypeEqual" => "_eq_object_type",
		"metadataObjectTypeIn" => "_in_object_type",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new MetadataProfileFilter();
	}
}
