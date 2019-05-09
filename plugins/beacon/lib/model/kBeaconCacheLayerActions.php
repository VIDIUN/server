<?php
/**
 * Class vBeaconCacheLayerActions
 *
 * Package and location is not indicated
 * Should not include any vidiun dependency in this class - to enable it to run in cache only mode
 */


require_once (dirname(__FILE__) . '/vBeacon.php');
require_once (dirname(__FILE__) . '/../../../../plugins/beacon/lib/model/enums/BeaconIndexType.php');
require_once (dirname(__FILE__) . '/../../../../plugins/beacon/lib/model/enums/BeaconObjectTypes.php');
require_once (dirname(__FILE__) . '/../../../../plugins/beacon/lib/model/vBeaconSearchQueryManger.php');

require_once (dirname(__FILE__) . '/../../../../plugins/queue/lib/QueueProvider.php');
require_once (dirname(__FILE__) . '/../../../../plugins/queue/providers/rabbit_mq/lib/RabbitMQProvider.php');
require_once (dirname(__FILE__) . '/../../../../plugins/queue/providers/rabbit_mq/lib/MultiCentersRabbitMQProvider.php');


class vBeaconCacheLayerActions
{
	const PARAM_EVENT_TYPE = "beacon:eventType";
	const PARAM_OBJECT_ID = "beacon:objectId";
	const PARAM_RELATED_OBJECT_TYPE = "beacon:relatedObjectType";
	const PARAM_PRIVATE_DATA = "beacon:privateData";
	const PARAM_RAW_DATA = "beacon:rawData";
	const PARAM_SHOULD_LOG = "shouldLog";
	const PARAM_VS_PARTNER_ID = "___cache___partnerId";
	const PARAM_IMPERSONATED_PARTNER_ID = "partnerId";
	
	public static function validateInputExists($params, $paramKey)
	{
		return !array_key_exists($paramKey, $params) || $params[$paramKey] == '';
	}
	
	public static function add($params)
	{
		if(is_null($params))
			throw new Exception("Params not provided");
		
		if(self::validateInputExists($params, vBeaconCacheLayerActions::PARAM_VS_PARTNER_ID))
			return false;
		
		if (self::validateInputExists($params, vBeaconCacheLayerActions::PARAM_EVENT_TYPE) ||
			self::validateInputExists($params, vBeaconCacheLayerActions::PARAM_OBJECT_ID) ||
			self::validateInputExists($params, vBeaconCacheLayerActions::PARAM_RELATED_OBJECT_TYPE)
		)
			return false;
		
		$partnerId =  $params[vBeaconCacheLayerActions::PARAM_VS_PARTNER_ID];
		if(isset($params[vBeaconCacheLayerActions::PARAM_IMPERSONATED_PARTNER_ID]))
			$partnerId = $params[vBeaconCacheLayerActions::PARAM_IMPERSONATED_PARTNER_ID];
		
		if(!$partnerId)
			return false;
		
		$beacon = new vBeacon($partnerId);
		$beacon->setObjectId($params[vBeaconCacheLayerActions::PARAM_OBJECT_ID]);
		$beacon->setEventType($params[vBeaconCacheLayerActions::PARAM_EVENT_TYPE]);
		$beacon->setRelatedObjectType($params[vBeaconCacheLayerActions::PARAM_RELATED_OBJECT_TYPE]);
		
		if(isset($params[vBeaconCacheLayerActions::PARAM_PRIVATE_DATA]))
			$beacon->setPrivateData($params[vBeaconCacheLayerActions::PARAM_PRIVATE_DATA]);
		
		if(isset($params[vBeaconCacheLayerActions::PARAM_RAW_DATA]))
			$beacon->setRawData($params[vBeaconCacheLayerActions::PARAM_RAW_DATA]);
		
		$shouldLog = false;
		if(isset($params[vBeaconCacheLayerActions::PARAM_SHOULD_LOG]) && $params[vBeaconCacheLayerActions::PARAM_SHOULD_LOG])
			$shouldLog = true;
		
		$queueProvider = self::loadQueueProvider();
		if(!$queueProvider)
			throw new Exception("Queue Provider could not be initialized");
		
		return $beacon->index($shouldLog, $queueProvider);
	}
	
	public static function loadQueueProvider()
	{
		$constructorArgs = array();
		$constructorArgs['exchangeName'] = vBeacon::BEACONS_EXCHANGE_NAME;
		if(!vConf::hasMap('rabbit_mq'))
		{
			return null;
		}
		
		$rabbitConfig = vConf::getMap('rabbit_mq');
		if(isset($rabbitConfig['multiple_dcs']) && $rabbitConfig['multiple_dcs'])
		{
			return new MultiCentersRabbitMQProvider($rabbitConfig, $constructorArgs);
		}
		
		return new RabbitMQProvider($rabbitConfig, $constructorArgs);
	}
}
