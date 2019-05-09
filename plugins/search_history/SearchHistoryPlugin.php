<?php
/**
 * @package plugins.searchHistory
 */
class SearchHistoryPlugin extends VidiunPlugin implements IVidiunPending, IVidiunServices, IVidiunEventConsumers
{

    const PLUGIN_NAME = 'searchHistory';
    const SEARCH_HISTORY_MANAGER = 'vESearchHistoryManager';

    /**
     * @return string the name of the plugin
     */
    public static function getPluginName()
    {
        return self::PLUGIN_NAME;
    }

    /**
     * @return array
     */
    public static function getEventConsumers()
    {
        return array(
            self::SEARCH_HISTORY_MANAGER,
        );
    }

    /* (non-PHPdoc)
    * @see IVidiunPending::dependsOn()
    */
    public static function dependsOn()
    {
        $rabbitMqDependency = new VidiunDependency(RabbitMQPlugin::getPluginName());
        $elasticSearchDependency = new VidiunDependency(ElasticSearchPlugin::getPluginName());
        return array($rabbitMqDependency, $elasticSearchDependency);
    }

    public static function getServicesMap()
    {
        $map = array(
            'SearchHistory' => 'ESearchHistoryService',
        );
        return $map;
    }

}
