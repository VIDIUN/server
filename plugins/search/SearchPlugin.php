<?php
/**
 * @package plugins.Search
 */

class SearchPlugin extends VidiunPlugin
{

    const PLUGIN_NAME = 'search';
    /**
     * @return string the name of the plugin
     */
    public static function getPluginName()
    {
        return self::PLUGIN_NAME;
    }
}
