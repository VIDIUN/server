<?php

/*
* @package plugins.widevine
* @subpackage model.enums
*/

class WidevineSchemeName implements IVidiunPluginEnum, DrmSchemeName
{
	const WIDEVINE = 'WIDEVINE';

	public static function getAdditionalValues()
	{
		return array
		(
			'WIDEVINE' => self::WIDEVINE,
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