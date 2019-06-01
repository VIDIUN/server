<?php

require_once(dirname(__FILE__) . '/../../../../infra/general/BaseEnum.php');
require_once(dirname(__FILE__) . '/../../../lib/enums/geoCoderType.php');
/**
 * @package Core
 * @subpackage AccessControl
 */
class vGeoCoderManager
{
	/**
	 * @param int $type of enum geoCoderType
	 * @return vGeoCoder
	 */
	public static function getGeoCoder($type = null)
	{
		if(!$type)
		{
			$type = geoCoderType::VIDIUN;
		}
			
		switch($type)
		{
		case geoCoderType::VIDIUN:
			// require direct path as the call may arrive for the caching layer
			require_once(dirname(__FILE__) . '/myIPGeocoder.class.php');
			return new myIPGeocoder();
			
		case geoCoderType::MAX_MIND:
			// require direct path as the call may arrive for the caching layer			
			require_once(dirname(__FILE__) . '/vMaxMindIPGeoCoder.php');
			return new vMaxMindIPGeocoder();
			
		case geoCoderType::DIGITAL_ELEMENT:
			// require direct path as the call may arrive for the caching layer			
			require_once(dirname(__FILE__) . '/vDigitalElementIPGeoCoder.php');
			return new vDigitalElementIPGeocoder();
			
		}
			
		//currently there aren't any GeoCoder plugins and the caching layer won't support auto loading them anyway
		//return VidiunPluginManager::loadObject('vGeoCoder', $type);
		return new myIPGeocoder();
	}
}