<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchCategoryEntryItem extends VidiunESearchAbstractEntryItem
{
	/**
	 * @var VidiunESearchCategoryEntryFieldName
	 */
	public $fieldName;

	/**
	 * @var VidiunCategoryEntryStatus
	 */
	public $categoryEntryStatus;

	private static $map_between_objects = array(
		'fieldName',
		'categoryEntryStatus',
	);

	private static $map_dynamic_enum = array();

	private static $map_field_enum = array(
		VidiunESearchCategoryEntryFieldName::ID => ESearchCategoryEntryFieldName::ID,
		VidiunESearchCategoryEntryFieldName::FULL_IDS => ESearchCategoryEntryFieldName::FULL_IDS,
		VidiunESearchCategoryEntryFieldName::NAME => ESearchCategoryEntryFieldName::NAME,
		VidiunESearchCategoryEntryFieldName::ANCESTOR_ID => ESearchCategoryEntryFieldName::ANCESTOR_ID,
		VidiunESearchCategoryEntryFieldName::ANCESTOR_NAME => ESearchCategoryEntryFieldName::ANCESTOR_NAME,
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
			$object_to_fill = ESearchCategoryEntryItemFactory::getCoreItemByFieldName($this->fieldName);

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
