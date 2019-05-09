<?php
/**
 * @package plugins.beacon
 * @subpackage api.objects
 */
class VidiunBeaconSearchScheduledResourceOrderByItem extends VidiunESearchOrderByItem
{
	/**
	 *  @var VidiunBeaconScheduledResourceOrderByFieldName
	 */
	public $sortField;

	private static $map_between_objects = array(
		'sortField',
	);

	private static $map_field_enum = array(
		VidiunBeaconScheduledResourceOrderByFieldName::STATUS => BeaconScheduledResourceOrderByFieldName::STATUS,
		VidiunBeaconScheduledResourceOrderByFieldName::RECORDING => BeaconScheduledResourceOrderByFieldName::RECORDING,
		VidiunBeaconScheduledResourceOrderByFieldName::RESOURCE_NAME => BeaconScheduledResourceOrderByFieldName::RESOURCE_NAME,
		VidiunBeaconScheduledResourceOrderByFieldName::UPDATED_AT => BeaconScheduledResourceOrderByFieldName::UPDATED_AT,
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
		{
			$object_to_fill = new vBeaconScheduledResourceOrderByItem();
		}

		return parent::toObject($object_to_fill, $props_to_skip);
	}

	public function getFieldEnumMap()
	{
		return self::$map_field_enum;
	}

	public function getItemFieldName()
	{
		return $this->sortField;
	}

}
