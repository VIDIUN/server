<?php

/**
 * Sending beacons on various objects
 * @package plugins.beacon
 */
class BeaconPlugin extends VidiunPlugin implements IVidiunServices, IVidiunPermissions, IVidiunPending
{
	const PLUGIN_NAME = "beacon";
	const BEACON_MANAGER = 'vBeaconManager';
	
	/* (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap()
	{
		$map = array(
			'beacon' => 'BeaconService',
		);
		return $map;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		return true;
	}
	
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
 	 * @see IVidiunPending::dependsOn()
 	*/
	public static function dependsOn()
	{
		$rabbitMqDependency = new VidiunDependency(RabbitMQPlugin::getPluginName());
		$elasticSearchDependency = new VidiunDependency(ElasticSearchPlugin::getPluginName());
		return array($rabbitMqDependency, $elasticSearchDependency);
	}
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
}
