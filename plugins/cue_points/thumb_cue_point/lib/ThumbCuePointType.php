<?php
/**
 * @package plugins.thumbCuePoint
 * @subpackage lib.enum
 */
class ThumbCuePointType implements IVidiunPluginEnum, CuePointType
{
	const THUMB = 'Thumb';
	
	public static function getAdditionalValues()
	{
		return array(
			'THUMB' => self::THUMB,
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