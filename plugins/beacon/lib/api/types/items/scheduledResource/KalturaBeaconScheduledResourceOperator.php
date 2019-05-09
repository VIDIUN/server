<?php
/**
 * @package plugins.beacon
 * @subpackage api.objects
 */
class VidiunBeaconScheduledResourceOperator extends VidiunBeaconScheduledResourceBaseItem
{
	/**
	 * @var VidiunESearchOperatorType
	 */
	public $operator;

	/**
	 *  @var VidiunBeaconScheduledResourceBaseItemArray
	 */
	public $searchItems;

	private static $map_between_objects = array(
		'operator',
		'searchItems',
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
			$object_to_fill = new ESearchOperator();
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}