<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchCategoryOrderByItem extends VidiunESearchOrderByItem
{
    /**
     *  @var VidiunESearchCategoryOrderByFieldName
     */
    public $sortField;

    private static $map_between_objects = array(
        'sortField',
    );

    private static $map_field_enum = array(
        VidiunESearchCategoryOrderByFieldName::UPDATED_AT => ESearchCategoryOrderByFieldName::UPDATED_AT,
        VidiunESearchCategoryOrderByFieldName::CREATED_AT => ESearchCategoryOrderByFieldName::CREATED_AT,
        VidiunESearchCategoryOrderByFieldName::ENTRIES_COUNT => ESearchCategoryOrderByFieldName::ENTRIES_COUNT,
        VidiunESearchCategoryOrderByFieldName::MEMBERS_COUNT => ESearchCategoryOrderByFieldName::MEMBERS_COUNT,
        VidiunESearchCategoryOrderByFieldName::NAME => ESearchCategoryOrderByFieldName::NAME,
    );

    public function getMapBetweenObjects()
    {
        return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
    }

    public function toObject($object_to_fill = null, $props_to_skip = array())
    {
        if (!$object_to_fill)
            $object_to_fill = new ESearchCategoryOrderByItem();
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
