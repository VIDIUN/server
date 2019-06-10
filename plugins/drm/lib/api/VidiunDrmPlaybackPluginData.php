<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDrmPlaybackPluginData extends VidiunPluginData{

    /**
     * @var VidiunDrmSchemeName
     */
    public $scheme;

    /**
     * @var string
     */
    public $licenseURL;

    private static $map_between_objects = array(
        'scheme',
        'licenseURL',
    );

    public function getMapBetweenObjects()
    {
        return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
    }

}