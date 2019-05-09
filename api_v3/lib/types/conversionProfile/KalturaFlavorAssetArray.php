<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunFlavorAssetArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunFlavorAssetArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
		    $nObj = VidiunFlavorAsset::getInstance($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunFlavorAsset");	
	}
}