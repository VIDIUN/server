<?php
/**
 * @package plugins.aptionAssetItemCuePoint
 * @subpackage lib.enum
 */
class CaptionAssetItemCuePointType implements IKalturaPluginEnum, CuePointType
{
	const CAPTION_ASSET_ITEM = 'CaptionAssetItem';
	
	public static function getAdditionalValues()
	{
		return array(
			'CAPTION_ASSET_ITEM' => self::CAPTION_ASSET_ITEM,
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