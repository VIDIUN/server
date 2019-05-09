<?php
/**
 * @package plugins.rabbitMQ
 */
class RabbitMQPlugin extends VidiunPlugin implements IVidiunPending, IVidiunObjectLoader, IVidiunQueuePlugin
{
	const PLUGIN_NAME = 'rabbitMQ';
	const QUEUE_PLUGIN_NAME = 'queue';
	const QUEUE_PLUGIN_VERSION_MAJOR = 1;
	const QUEUE_PLUGIN_VERSION_MINOR = 0;
	const QUEUE_PLUGIN_VERSION_BUILD = 0;
	
	/*
	 * (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/*
	 * (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'QueueProvider' && (is_null($enumValue) || $enumValue == self::getRabbitMQProviderTypeCoreValue(RabbitMQProviderType::RABBITMQ)))
		{
			if(!vConf::hasMap('rabbit_mq'))
			{
				throw new vCoreException("RabbitMQ configuration file (rabbit_mq.ini) wasn't found!");
			}
			$rabbitConfig = vConf::getMap('rabbit_mq');
			
			if(isset($rabbitConfig['multiple_dcs']) && $rabbitConfig['multiple_dcs'])
			{
				return 'MultiCentersRabbitMQProvider';
			}
			
			return 'RabbitMQProvider';
		}
	}
	
	/*
	 * (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'QueueProvider' && (is_null($enumValue) || $enumValue == self::getRabbitMQProviderTypeCoreValue(RabbitMQProviderType::RABBITMQ)))
		{
			if(!vConf::hasMap('rabbit_mq'))
			{
				throw new vCoreException("RabbitMQ configuration file (rabbit_mq.ini) wasn't found!");
			}
			
			$rabbitConfig = vConf::getMap('rabbit_mq');
			if(isset($rabbitConfig['multiple_dcs']) && $rabbitConfig['multiple_dcs'])
			{
				return new MultiCentersRabbitMQProvider($rabbitConfig, $constructorArgs);
			}
			
			return new RabbitMQProvider($rabbitConfig, $constructorArgs);
		}
		
		return null;
	}
	
	/**
	 *
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getPushNotificationTemplateTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('QueueProviderType', $value);
	}
	
	/*
	 * (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$minVersion = new VidiunVersion(self::QUEUE_PLUGIN_VERSION_MAJOR, self::QUEUE_PLUGIN_VERSION_MINOR, self::QUEUE_PLUGIN_VERSION_BUILD);
		$dependency = new VidiunDependency(self::QUEUE_PLUGIN_NAME, $minVersion);
		
		return array($dependency);
	}
}
