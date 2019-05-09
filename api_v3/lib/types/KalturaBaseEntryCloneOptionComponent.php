<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBaseEntryCloneOptionComponent extends VidiunBaseEntryCloneOptionItem
{
    /**
     *
     * @var VidiunBaseEntryCloneOptions
     */
    public $itemType;

    /**
     * condition rule (include/exclude)
     *
     * @var VidiunCloneComponentSelectorType
     */
    public $rule;



    private static $mapBetweenObjects = array
    (
        'itemType',
        'rule',
    );

    /**
     * @return array
     */
    public function getMapBetweenObjects()
    {
        return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
    }

    public function toObject($dbObject = null, $skip = array())
    {
        if(!$dbObject)
            $dbObject = new vBaseEntryCloneOptionComponent();

        return parent::toObject($dbObject, $skip);
    }

    /* (non-PHPdoc)
 * @see VidiunObject::fromObject()
 */
    public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
    {
        /** @var $dbObject vBaseEntryCloneOptionComponent */
        parent::doFromObject($dbObject, $responseProfile);
    }
    public function __construct()
    {
    }



}