<?php
/**
 * Enable event cue point objects management on entry objects
 * @package plugins.EventCuePoint
 */
class EventCuePointPlugin extends BaseCuePointPlugin implements IVidiunCuePoint, IVidiunEventConsumers
{
	const PLUGIN_NAME = 'eventCuePoint';
	const CUE_POINT_VERSION_MAJOR = 1;
	const CUE_POINT_VERSION_MINOR = 0;
	const CUE_POINT_VERSION_BUILD = 0;
	const CUE_POINT_NAME = 'cuePoint';
	
	const EVENT_CUE_POINT_CONSUMER = 'vEventCuePointConsumer';
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		return true;
	}

	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('EventCuePointType');
	
		if($baseEnumName == 'CuePointType')
			return array('EventCuePointType');
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$cuePointVersion = new VidiunVersion(
			self::CUE_POINT_VERSION_MAJOR,
			self::CUE_POINT_VERSION_MINOR,
			self::CUE_POINT_VERSION_BUILD);
			
		$dependency = new VidiunDependency(self::CUE_POINT_NAME, $cuePointVersion);
		return array($dependency);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'VidiunCuePoint' && $enumValue == self::getCuePointTypeCoreValue(EventCuePointType::EVENT))
			return new VidiunEventCuePoint();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'CuePoint' && $enumValue == self::getCuePointTypeCoreValue(EventCuePointType::EVENT))
			return 'EventCuePoint';
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePoint::getCuePointTypeCoreValue()
	 */
	public static function getCuePointTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('CuePointType', $value);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePoint::getApiValue()
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	public static function contributeToSchema($type)
	{
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	*/
	public static function getEventConsumers()
	{
		return array(
				self::EVENT_CUE_POINT_CONSUMER
		);
	}
	
	public static function getTypesToIndexOnEntry()
	{
		return array();
	}

	public static function shouldCloneByProperty(entry $entry)
	{
		return false;
	}

	public static function getTypesToElasticIndexOnEntry()
	{
		return array();
	}
}
