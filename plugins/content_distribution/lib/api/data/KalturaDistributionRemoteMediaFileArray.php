<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunDistributionRemoteMediaFileArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDistributionRemoteMediaFileArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = new VidiunDistributionRemoteMediaFile();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunDistributionRemoteMediaFile");	
	}
}