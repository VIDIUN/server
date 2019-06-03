<?php
/**
 * @package plugins.freewheelDistribution
 * @subpackage api.objects
 */
class VidiunFreewheelDistributionAssetPathArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunFreewheelDistributionAssetPathArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = new VidiunFreewheelDistributionAssetPath();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunFreewheelDistributionAssetPath");	
	}
}