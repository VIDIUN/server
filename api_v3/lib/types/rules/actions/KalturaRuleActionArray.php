<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunRuleActionArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunRuleActionArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = self::getInstanceByDbObject($obj);
			if(!$nObj)
				throw new vCoreException("No API object found for core object [" . get_class($obj) . "] with type [" . $obj->getType() . "]", vCoreException::OBJECT_API_TYPE_NOT_FOUND);
				
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}

	static function getInstanceByDbObject(vRuleAction $dbObject)
	{
		switch($dbObject->getType())
		{
			case RuleActionType::BLOCK:
				return new VidiunAccessControlBlockAction();
			case RuleActionType::PREVIEW:
				return new VidiunAccessControlPreviewAction();
			case RuleActionType::LIMIT_FLAVORS:
				return new VidiunAccessControlLimitFlavorsAction();
			case RuleActionType::ADD_TO_STORAGE:
				return new VidiunStorageAddAction();	
			case RuleActionType::LIMIT_DELIVERY_PROFILES:
				return new VidiunAccessControlLimitDeliveryProfilesAction();
			case RuleActionType::SERVE_FROM_REMOTE_SERVER:
				return new VidiunAccessControlServeRemoteEdgeServerAction();
			case RuleActionType::REQUEST_HOST_REGEX:
				return new VidiunAccessControlModifyRequestHostRegexAction();
			case RuleActionType::LIMIT_THUMBNAIL_CAPTURE:
				return new VidiunAccessControlLimitThumbnailCaptureAction();
			default:
				return VidiunPluginManager::loadObject('VidiunRuleAction', $dbObject->getType());
		}		
	}
		
	public function __construct()
	{
		parent::__construct("VidiunRuleAction");	
	}
}