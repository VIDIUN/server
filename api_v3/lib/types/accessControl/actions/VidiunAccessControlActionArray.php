<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use VidiunRuleActionArray
 */
class VidiunAccessControlActionArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunAccessControlActionArray();
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
			case RuleActionType::LIMIT_THUMBNAIL_CAPTURE:
				return new VidiunAccessControlLimitThumbnailCaptureAction();
			default:
				return VidiunPluginManager::loadObject('VidiunAccessControlAction', $dbObject->getType());
		}
	}
		
	public function __construct()
	{
		parent::__construct("VidiunAccessControlAction");	
	}
}