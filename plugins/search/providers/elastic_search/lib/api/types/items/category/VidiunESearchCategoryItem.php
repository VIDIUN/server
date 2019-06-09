<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchCategoryItem extends VidiunESearchAbstractCategoryItem
{

	/**
	 * @var VidiunESearchCategoryFieldName
	 */
	public $fieldName;

	private static $map_between_objects = array(
		'fieldName'
	);

	private static $map_dynamic_enum = array();

	private static $map_field_enum = array(
		VidiunESearchCategoryFieldName::ID => ESearchCategoryFieldName::ID,
		VidiunESearchCategoryFieldName::PRIVACY => ESearchCategoryFieldName::PRIVACY,
		VidiunESearchCategoryFieldName::PRIVACY_CONTEXT => ESearchCategoryFieldName::PRIVACY_CONTEXT,
		VidiunESearchCategoryFieldName::PRIVACY_CONTEXTS => ESearchCategoryFieldName::PRIVACY_CONTEXTS,
		VidiunESearchCategoryFieldName::PARENT_ID => ESearchCategoryFieldName::PARENT_ID,
		VidiunESearchCategoryFieldName::DEPTH => ESearchCategoryFieldName::DEPTH,
		VidiunESearchCategoryFieldName::NAME => ESearchCategoryFieldName::NAME,
		VidiunESearchCategoryFieldName::FULL_NAME => ESearchCategoryFieldName::FULL_NAME,
		VidiunESearchCategoryFieldName::FULL_IDS => ESearchCategoryFieldName::FULL_IDS,
		VidiunESearchCategoryFieldName::DESCRIPTION => ESearchCategoryFieldName::DESCRIPTION,
		VidiunESearchCategoryFieldName::TAGS => ESearchCategoryFieldName::TAGS,
		VidiunESearchCategoryFieldName::DISPLAY_IN_SEARCH => ESearchCategoryFieldName::DISPLAY_IN_SEARCH,
		VidiunESearchCategoryFieldName::INHERITANCE_TYPE => ESearchCategoryFieldName::INHERITANCE_TYPE,
		VidiunESearchCategoryFieldName::USER_ID => ESearchCategoryFieldName::VUSER_ID,
		VidiunESearchCategoryFieldName::REFERENCE_ID => ESearchCategoryFieldName::REFERENCE_ID,
		VidiunESearchCategoryFieldName::INHERITED_PARENT_ID => ESearchCategoryFieldName::INHERITED_PARENT_ID,
		VidiunESearchCategoryFieldName::MODERATION => ESearchCategoryFieldName::MODERATION,
		VidiunESearchCategoryFieldName::CONTRIBUTION_POLICY => ESearchCategoryFieldName::CONTRIBUTION_POLICY,
		VidiunESearchCategoryFieldName::ENTRIES_COUNT => ESearchCategoryFieldName::ENTRIES_COUNT,
		VidiunESearchCategoryFieldName::DIRECT_ENTRIES_COUNT => ESearchCategoryFieldName::DIRECT_ENTRIES_COUNT,
		VidiunESearchCategoryFieldName::DIRECT_SUB_CATEGORIES_COUNT => ESearchCategoryFieldName::DIRECT_SUB_CATEGORIES_COUNT,
		VidiunESearchCategoryFieldName::MEMBERS_COUNT => ESearchCategoryFieldName::MEMBERS_COUNT,
		VidiunESearchCategoryFieldName::PENDING_MEMBERS_COUNT => ESearchCategoryFieldName::PENDING_MEMBERS_COUNT,
		VidiunESearchCategoryFieldName::PENDING_ENTRIES_COUNT => ESearchCategoryFieldName::PENDING_ENTRIES_COUNT,
		VidiunESearchCategoryFieldName::CREATED_AT => ESearchCategoryFieldName::CREATED_AT,
		VidiunESearchCategoryFieldName::UPDATED_AT => ESearchCategoryFieldName::UPDATED_AT,
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (!$object_to_fill)
			$object_to_fill = new ESearchCategoryItem();
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
