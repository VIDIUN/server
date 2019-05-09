<?php
/**
 * @package plugins.transcript
 * @subpackage api.objects
 */
class VidiunTranscriptAssetArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunTranscriptAssetArray();
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
		parent::__construct("VidiunTranscriptAsset");
	}
}
