<?php
/**
 * @package plugins.caption
 * @subpackage api.objects
 */
class VidiunCaptionPlaybackPluginDataArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunCaptionPlaybackPluginDataArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = new VidiunCaptionPlaybackPluginData();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}

	public function __construct()
	{
		parent::__construct("VidiunCaptionPlaybackPluginData");
	}
}