<?php

/**
 * @package Core
 * @subpackage externalWidgets
 */
class embedPakhshkitJsSourceMapsAction extends sfAction
{
    public function execute()
    {
        $sourceMapsCache = vCacheManager::getSingleLayerCache(vCacheManager::CACHE_TYPE_PAKHSHKIT_JS_SOURCE_MAP);
        if (!$sourceMapsCache)
            VExternalErrors::dieError(VExternalErrors::BUNDLE_CREATION_FAILED, "PakhshKit source maps cache not defined");

        //Get cacheKey
        $cacheKey = $this->getRequestParameter('path');
        if (!$cacheKey)
            VExternalErrors::dieError(VExternalErrors::MISSING_PARAMETER, 'path');
        
        //cacheKey should be base64 encoded string which ends with min.js.map
        if (!preg_match('`^[a-zA-Z0-9+/]+={0,2}`', $cacheKey)) 
        {
            VExternalErrors::dieGracefully("Wrong source map name pattern");
        }
        
        $sourceMap = $sourceMapsCache->get($cacheKey);
        header("Content-Type:application/octet-stream");

        echo($sourceMap);
        VExternalErrors::dieGracefully();
    }
}
