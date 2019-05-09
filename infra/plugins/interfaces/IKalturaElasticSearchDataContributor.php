<?php
/**
 * Enable the plugin to return additional data to be saved on indexed object
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunElasticSearchDataContributor extends IVidiunBase
{
    /**
     * Return elasticsearch data to be associated with the object
     *
     * @param BaseObject $object
     * @return ArrayObject
     */
    public static function getElasticSearchData(BaseObject $object);
}