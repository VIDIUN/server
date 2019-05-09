<?php
/**
 * @package plugins.schedule
 */
class SchedulePlugin extends VidiunPlugin implements IVidiunServices, IVidiunEventConsumers, IVidiunVersion, IVidiunObjectLoader
{
	const PLUGIN_NAME = 'schedule';
	const PLUGIN_VERSION_MAJOR = 1;
	const PLUGIN_VERSION_MINOR = 0;
	const PLUGIN_VERSION_BUILD = 0;
	const SCHEDULE_EVENTS_CONSUMER = 'vScheduleEventsConsumer';
	const ICAL_RESPONSE_TYPE = 'ical';
	
	public static function dependsOn()
	{
		$metadataDependency = new VidiunDependency(self::METADATA_PLUGIN_NAME);
		
		return array($metadataDependency);
	}
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/*
	 * (non-PHPdoc)
	 * @see IVidiunVersion::getVersion()
	 */
	public static function getVersion()
	{
		return new VidiunVersion(self::PLUGIN_VERSION_MAJOR, self::PLUGIN_VERSION_MINOR, self::PLUGIN_VERSION_BUILD);
	}
	
	/*
	 * (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap()
	{
		$map = array('scheduleEvent' => 'ScheduleEventService', 'scheduleResource' => 'ScheduleResourceService', 'scheduleEventResource' => 'ScheduleEventResourceService');
		return $map;
	}
	
	/*
	 * (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(self::SCHEDULE_EVENTS_CONSUMER);
	}
	
	/*
	 * (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'VidiunSerializer' && $enumValue == self::ICAL_RESPONSE_TYPE)
			return new VidiunICalSerializer();
		
		return null;
	}
	
	/*
	 * (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'VidiunSerializer' && $enumValue == self::ICAL_RESPONSE_TYPE)
			return 'VidiunICalSerializer';
		
		return null;
	}

	public static function getSingleScheduleEventMaxDuration()
	{
		$maxSingleScheduleEventDuration = 60 * 60 * 24; // 24 hours
		return vConf::get('max_single_schedule_event_duration', 'local', $maxSingleScheduleEventDuration);
	}

	public static function getScheduleEventmaxDuration()
	{
		$maxDuration = 60 * 60 * 24 * 365 * 2; // two years
		return vConf::get('max_schedule_event_duration', 'local', $maxDuration);
	}
	
	public static function getScheduleEventmaxRecurrences()
	{
		$maxRecurrences = 1000;
		return vConf::get('max_schedule_event_recurrences', 'local', $maxRecurrences);
	}
}
