<?php
/**
 * @package plugins.youtubeApiDistribution
 * @subpackage api.objects
 */
class VidiunYouTubeApiCaptionDistributionInfoArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunYouTubeApiCaptionDistributionInfoArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = new VidiunYouTubeApiCaptionDistributionInfo();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunYouTubeApiCaptionDistributionInfo");	
	}
}