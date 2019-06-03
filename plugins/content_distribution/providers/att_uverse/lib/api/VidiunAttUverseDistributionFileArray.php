<?php
/**
 * @package plugins.attUverseDistribution
 * @subpackage api.objects
 */
class VidiunAttUverseDistributionFileArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunAttUverseDistributionFileArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = new VidiunAttUverseDistributionFile();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}

	public function __construct()
	{
		parent::__construct("VidiunAttUverseDistributionFile");	
	}
}