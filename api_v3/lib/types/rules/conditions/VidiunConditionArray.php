<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunConditionArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunConditionArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = self::getInstanceByDbObject($obj);
			if(!$nObj)
			{
				VidiunLog::alert("Object [" . get_class($obj) . "] type [" . $obj->getType() . "] could not be translated to API object");
				continue;
			}
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}

	static function getInstanceByDbObject(vCondition $dbObject)
	{
		switch($dbObject->getType())
		{
			case ConditionType::AUTHENTICATED:
				return new VidiunAuthenticatedCondition();
			case ConditionType::COUNTRY:
				return new VidiunCountryCondition();
			case ConditionType::IP_ADDRESS:
				return new VidiunIpAddressCondition();
			case ConditionType::SITE:
				return new VidiunSiteCondition();
			case ConditionType::USER_AGENT:
				return new VidiunUserAgentCondition();
			case ConditionType::FIELD_COMPARE:
				return new VidiunFieldCompareCondition();
			case ConditionType::FIELD_MATCH:
				return new VidiunFieldMatchCondition();
			case ConditionType::ASSET_PROPERTIES_COMPARE:
				return new VidiunAssetPropertiesCompareCondition();
			case ConditionType::USER_ROLE:
				return new VidiunUserRoleCondition();
			case ConditionType::GEO_DISTANCE:
				return new VidiunGeoDistanceCondition();
			case ConditionType::OR_OPERATOR:
			    return new VidiunOrCondition();
			case ConditionType::HASH:
			    return new VidiunHashCondition();
			case ConditionType::DELIVERY_PROFILE:
				return new VidiunDeliveryProfileCondition();
			case ConditionType::ACTIVE_EDGE_VALIDATE:
				return new VidiunValidateActiveEdgeCondition();
			case ConditionType::ANONYMOUS_IP:
				return new VidiunAnonymousIPCondition();
			case ConditionType::ASSET_TYPE:
				return new VidiunAssetTypeCondition();
			case ConditionType::BOOLEAN:
				return new VidiunBooleanEventNotificationCondition();
			default:
			     return VidiunPluginManager::loadObject('VidiunCondition', $dbObject->getType());
		}
	}
		
	public function __construct()
	{
		parent::__construct("VidiunCondition");	
	}
}