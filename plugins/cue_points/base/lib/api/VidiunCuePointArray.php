<?php
/**
 * @package plugins.cuePoint
 * @subpackage api.objects
 */
class VidiunCuePointArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunCuePointArray();
		if ($arr == null)
			return $newArr;
		
		foreach ($arr as $obj)
		{
    		$nObj = VidiunCuePoint::getInstance($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunCuePoint");	
	}
}
