<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBaseEntryArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$isAdmin = vCurrentContext::$is_admin_session;
		$newArr = new VidiunBaseEntryArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = VidiunEntryFactory::getInstanceByType($obj->getType(), $isAdmin);
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunBaseEntry");	
	}
}