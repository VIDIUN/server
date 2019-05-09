<?php
/**
 * @package plugins.exampleDistribution
 * @subpackage api.objects
 */
class VidiunExampleDistributionAssetPathArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunExampleDistributionAssetPathArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = new VidiunExampleDistributionAssetPath();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunExampleDistributionAssetPath");	
	}
}