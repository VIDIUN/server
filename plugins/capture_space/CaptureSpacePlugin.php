<?php
/**
 * @package plugins.captureSpace
 */
class CaptureSpacePlugin extends VidiunPlugin implements IVidiunServices {

    const PLUGIN_NAME = "captureSpace";

    public static function getPluginName() {
        return self::PLUGIN_NAME;
    }

    public static function getServicesMap()
    {
        $map = array(
            'captureSpace' => 'CaptureSpaceService',
        );
        return $map;
    }
}

    

