<?php
/**
 * @package plugins.externalMedia
 * @subpackage api.objects
 */
class VidiunExternalMediaEntryArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunExternalMediaEntryArray();
		if($arr == null)
			return $newArr;
		
		foreach($arr as $obj)
		{
    		$nObj = VidiunEntryFactory::getInstanceByType($obj->getType());
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		parent::__construct("VidiunExternalMediaEntry");	
	}
}