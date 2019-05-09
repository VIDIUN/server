<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchItemDataResult extends VidiunObject
{
	/**
	 * @var int
	 */
	public $totalCount;

	/**
	 * @var VidiunESearchItemDataArray
	 */
	public $items;

	/**
	 * @var string
	 */
	public $itemsType;

	private static $map_between_objects = array(
		'totalCount',
		'items',
		'itemsType',
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

}
