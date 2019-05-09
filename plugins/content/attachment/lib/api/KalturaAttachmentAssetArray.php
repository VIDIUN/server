<?php
/**
 * @package plugins.attachment
 * @subpackage api.objects
 */
class VidiunAttachmentAssetArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunAttachmentAssetArray();
		if ($arr == null)
			return $newArr;
	
		foreach ($arr as $obj)
		{
			$nObj = VidiunAsset::getInstance($obj);
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunAttachmentAsset");	
	}
}
