<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunLiveStreamParamsArray extends VidiunTypedArray {
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunLiveStreamParamsArray();
		if ($arr == null)
			return $newArr;
	
		foreach ($arr as $obj)
		{
			$nObj = new VidiunLiveStreamParams();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
	
		return $newArr;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunLiveStreamParams");
	}
}