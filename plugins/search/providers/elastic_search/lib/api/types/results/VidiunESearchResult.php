<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
abstract class VidiunESearchResult extends VidiunObject
{
    /**
     * @var VidiunESearchHighlightArray
     */
    public $highlight;

    /**
     * @var VidiunESearchItemDataResultArray
     */
    public $itemsData;

    private static $map_between_objects = array(
        'highlight',
        'itemsData',
    );

    protected function getMapBetweenObjects()
    {
        return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
    }

}
