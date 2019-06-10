<?php
/**
 * @package plugins.virusScan
 * @subpackage api.objects
 */
class VidiunVirusScanEngineType extends VidiunDynamicEnum implements VirusScanEngineType
{
	/**
	 * @return string
	 */
	public static function getEnumClass()
	{
		return 'VirusScanEngineType';
	}
}