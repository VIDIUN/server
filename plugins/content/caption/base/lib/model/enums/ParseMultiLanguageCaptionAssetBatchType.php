<?php
/**
 * @package plugins.caption
  * @subpackage api.enum
   */
   class ParseMultiLanguageCaptionAssetBatchType implements IVidiunPluginEnum, BatchJobType
   {
        const PARSE_MULTI_LANGUAGE_CAPTION_ASSET = 'parsemultilanguagecaptionasset';

        public static function getAdditionalValues()
        {
            return array(
                'PARSE_MULTI_LANGUAGE_CAPTION_ASSET' => self::PARSE_MULTI_LANGUAGE_CAPTION_ASSET
            );
         }

        /**
        * @return array
        */
        public static function getAdditionalDescriptions()
        {
            return array();
        }
    }

