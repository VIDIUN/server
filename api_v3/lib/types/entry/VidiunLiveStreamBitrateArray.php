<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunLiveStreamBitrateArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunLiveStreamBitrateArray();
		if ($arr == null)
			return $newArr;
			
		foreach ($arr as $obj)
		{
			$nObj = new VidiunLiveStreamBitrate();
			$nObj->fromArray($obj);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunLiveStreamBitrate");	
	}
}
