<?php
/**
 * @package plugins.caption
 * @subpackage api.objects
 */
class VidiunCaptionAssetArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunCaptionAssetArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = new VidiunCaptionAsset();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunCaptionAsset");	
	}
}