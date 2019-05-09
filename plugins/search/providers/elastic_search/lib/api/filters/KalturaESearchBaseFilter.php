<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.filters
 */
abstract class VidiunESearchBaseFilter extends VidiunObject
{
	private static $mapBetweenObjects = array();

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}

}
