<?php
/**
 * @package plugins.cue_points
 * @subpackage api.objects
 */
class VidiunMultiClipCopyCuePointsJobData extends VidiunCopyCuePointsJobData
{
    
    /**
     *  an array of source start time and duration
     * @var VidiunClipDescriptionArray
     */
    public $clipsDescriptionArray;

    private static $map_between_objects = array
    (
        'clipsDescriptionArray',
    );

    /* (non-PHPdoc)
     * @see VidiunObject::getMapBetweenObjects()
     */
    public function getMapBetweenObjects ( )
    {
        return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
    }

    /* (non-PHPdoc)
     * @see VidiunObject::toObject()
     */
    public function toObject($dbData = null, $props_to_skip = array())
    {
        if(is_null($dbData))
            $dbData = new vMultiClipCopyCuePointsJobData();

        return parent::toObject($dbData, $props_to_skip);
    }
}