<?php

/**
 * Array of asset distribution rules
 *
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunAssetDistributionRulesArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunAssetDistributionRulesArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = new VidiunAssetDistributionRule();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}

	public function __construct()
	{
		parent::__construct("VidiunAssetDistributionRule");
	}
}