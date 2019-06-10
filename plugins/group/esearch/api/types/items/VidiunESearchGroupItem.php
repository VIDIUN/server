<?php
/**
 * @package plugins.group
 * @subpackage api.objects
 */
class VidiunESearchGroupItem extends VidiunESearchAbstractGroupItem
{
	/**
	 * @var VidiunESearchGroupFieldName
	 */
	public $fieldName;

	private static $map_between_objects = array(
		'fieldName'
	);

	private static $map_dynamic_enum = array();

	private static $map_field_enum = array(
		VidiunESearchGroupFieldName::SCREEN_NAME => ESearchUserFieldName::SCREEN_NAME,
		VidiunESearchGroupFieldName::EMAIL => ESearchUserFieldName::EMAIL,
		VidiunESearchGroupFieldName::TAGS => ESearchUserFieldName::TAGS,
		VidiunESearchGroupFieldName::UPDATED_AT => ESearchUserFieldName::UPDATED_AT,
		VidiunESearchGroupFieldName::CREATED_AT => ESearchUserFieldName::CREATED_AT,
		VidiunESearchGroupFieldName::LAST_NAME => ESearchUserFieldName::LAST_NAME,
		VidiunESearchGroupFieldName::FIRST_NAME => ESearchUserFieldName::FIRST_NAME,
		VidiunESearchGroupFieldName::PERMISSION_NAMES => ESearchUserFieldName::PERMISSION_NAMES,
		VidiunESearchGroupFieldName::GROUP_IDS => ESearchUserFieldName::GROUP_IDS,
		VidiunESearchGroupFieldName::ROLE_IDS => ESearchUserFieldName::ROLE_IDS,
		VidiunESearchGroupFieldName::USER_ID => ESearchUserFieldName::PUSER_ID,
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
			$object_to_fill = new ESearchUserItem();
		return parent::toObject($object_to_fill, $props_to_skip);
	}

	protected function getItemFieldName()
	{
		return $this->fieldName;
	}

	protected function getDynamicEnumMap()
	{
		return self::$map_dynamic_enum;
	}

	protected function getFieldEnumMap()
	{
		return self::$map_field_enum;
	}


}