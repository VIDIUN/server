<?php
/**
 * @package plugins.tvinciDistribution
 * @subpackage api.objects
 */
class VidiunTvinciDistributionTagArray extends VidiunTypedArray
{
	public function __construct()
	{
		parent::__construct("VidiunTvinciDistributionTag");
	}
	
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunTvinciDistributionTagArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = new VidiunTvinciDistributionTag();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}
}