<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchEntryOrderByItem extends VidiunESearchOrderByItem
{
    /**
     *  @var VidiunESearchEntryOrderByFieldName
     */
    public $sortField;

    private static $map_between_objects = array(
        'sortField',
    );

    private static $map_field_enum = array(
        VidiunESearchEntryOrderByFieldName::CREATED_AT => ESearchEntryOrderByFieldName::CREATED_AT,
        VidiunESearchEntryOrderByFieldName::UPDATED_AT => ESearchEntryOrderByFieldName::UPDATED_AT,
        VidiunESearchEntryOrderByFieldName::END_DATE => ESearchEntryOrderByFieldName::END_DATE,
        VidiunESearchEntryOrderByFieldName::START_DATE => ESearchEntryOrderByFieldName::START_DATE,
        VidiunESearchEntryOrderByFieldName::NAME => ESearchEntryOrderByFieldName::NAME,
        VidiunESearchEntryOrderByFieldName::VIEWS => ESearchEntryOrderByFieldName::VIEWS,
        VidiunESearchEntryOrderByFieldName::VOTES => ESearchEntryOrderByFieldName::VOTES,
        VidiunESearchEntryOrderByFieldName::PLAYS => ESearchEntryOrderByFieldName::PLAYS,
        VidiunESearchEntryOrderByFieldName::LAST_PLAYED_AT => ESearchEntryOrderByFieldName::LAST_PLAYED_AT,
        VidiunESearchEntryOrderByFieldName::PLAYS_LAST_30_DAYS => ESearchEntryOrderByFieldName::PLAYS_LAST_30_DAYS,
        VidiunESearchEntryOrderByFieldName::VIEWS_LAST_30_DAYS => ESearchEntryOrderByFieldName::VIEWS_LAST_30_DAYS,
        VidiunESearchEntryOrderByFieldName::PLAYS_LAST_7_DAYS => ESearchEntryOrderByFieldName::PLAYS_LAST_7_DAYS,
        VidiunESearchEntryOrderByFieldName::VIEWS_LAST_7_DAYS => ESearchEntryOrderByFieldName::VIEWS_LAST_7_DAYS,
        VidiunESearchEntryOrderByFieldName::PLAYS_LAST_1_DAY => ESearchEntryOrderByFieldName::PLAYS_LAST_1_DAY,
        VidiunESearchEntryOrderByFieldName::VIEWS_LAST_1_DAY => ESearchEntryOrderByFieldName::VIEWS_LAST_1_DAY,
    );

    public function getMapBetweenObjects()
    {
        return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
    }

    public function toObject($object_to_fill = null, $props_to_skip = array())
    {
        if (!$object_to_fill)
            $object_to_fill = new ESearchEntryOrderByItem();
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
