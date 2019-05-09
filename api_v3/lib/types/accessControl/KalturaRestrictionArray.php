<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use VidiunRuleArray instead
 */
class VidiunRestrictionArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunRestrictionArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = self::getInstanceByDbObject($obj);
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}

	static function getInstanceByDbObject(vAccessControlRestriction $dbObject)
	{
		$objectClass = get_class($dbObject);
		switch($objectClass)
		{
			case "vAccessControlSiteRestriction":
				return new VidiunSiteRestriction();
			case "vAccessControlCountryRestriction":
				return new VidiunCountryRestriction();
			case "vAccessControlSessionRestriction":
				return new VidiunSessionRestriction();
			case "vAccessControlPreviewRestriction":
				return new VidiunPreviewRestriction();
			case "vAccessControlIpAddressRestriction":
				return new VidiunIpAddressRestriction();
			case "vAccessControlUserAgentRestriction":
				return new VidiunUserAgentRestriction();
			case "vAccessControlLimitFlavorsRestriction":
				return new VidiunLimitFlavorsRestriction();
			default:
				VidiunLog::err("Access control rule type [$objectClass] could not be loaded");
				return null;
		}
	}
	
	public function __construct()
	{
		parent::__construct("VidiunBaseRestriction");	
	}
}