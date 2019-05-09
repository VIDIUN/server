<?php
/**
 * @package plugins.dailymotionDistribution
 * @subpackage api.objects
 */
class VidiunDailymotionDistributionCaptionInfoArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDailymotionDistributionCaptionInfoArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = new VidiunDailymotionDistributionCaptionInfo();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunDailymotionDistributionCaptionInfo");	
	}
}