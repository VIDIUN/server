<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchGroupOrderByItem extends VidiunESearchOrderByItem
{
	/**
	 *  @var VidiunESearchGroupOrderByFieldName
	 */
	public $sortField;

	private static $map_between_objects = array(
		'sortField',
	);

	private static $map_field_enum = array(
		VidiunESearchGroupOrderByFieldName::CREATED_AT => ESearchGroupOrderByFieldName::CREATED_AT,
		VidiunESearchGroupOrderByFieldName::UPDATED_AT => ESearchGroupOrderByFieldName::UPDATED_AT,
		VidiunESearchGroupOrderByFieldName::SCREEN_NAME => ESearchGroupOrderByFieldName::SCREEN_NAME,
		VidiunESearchGroupOrderByFieldName::USER_ID => ESearchGroupOrderByFieldName::USER_ID,
		VidiunESearchGroupOrderByFieldName::MEMBERS_COUNT => ESearchGroupOrderByFieldName::MEMBERS_COUNT,
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
			$object_to_fill = new ESearchGroupOrderByItem();
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
