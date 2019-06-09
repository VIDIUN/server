<?php

/**
 * @package plugins.beacon
 * @subpackage api.objects
 */
class VidiunBeaconArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunBeaconArray();
		if ($arr == null)
			return $newArr;
		
		foreach ($arr as $obj) 
		{
			$nObj = new VidiunBeacon();
			$nObj->fromArray($obj);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		parent::__construct("VidiunBeacon");
	}
}