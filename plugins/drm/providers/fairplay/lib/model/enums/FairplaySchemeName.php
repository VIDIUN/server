<?php

/*
* @package plugins.fairplay
* @subpackage model.enums
*/

class FairplaySchemeName implements IVidiunPluginEnum, DrmSchemeName
{
	const FAIRPLAY = 'FAIRPLAY';

	public static function getAdditionalValues()
	{
		return array
		(
			'FAIRPLAY' => self::FAIRPLAY,
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