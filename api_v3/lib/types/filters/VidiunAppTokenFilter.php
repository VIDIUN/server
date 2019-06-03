<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunAppTokenFilter extends VidiunAppTokenBaseFilter
{
	static private $map_between_objects = array
	(
		"sessionUserIdEqual" => "_eq_vuser_id",
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
		return new appTokenFilter();
	}
}
