<?php
/**
 * @package infra
 * @subpackage Plugins
 */
class ParentObjectFeatureType implements IVidiunPluginEnum, ObjectFeatureType
{
    const PARENT = 'Parent';

    /* (non-PHPdoc)
     * @see IVidiunPluginEnum::getAdditionalValues()
     */
    public static function getAdditionalValues()
    {
        return array
        (
            'PARENT' => self::PARENT,
        );

    }

    /* (non-PHPdoc)
     * @see IVidiunPluginEnum::getAdditionalDescriptions()
     */
    public static function getAdditionalDescriptions() {
        return array();

    }
}