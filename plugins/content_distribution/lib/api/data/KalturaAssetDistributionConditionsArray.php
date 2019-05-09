<?php

/**
 * Array of asset distribution conditions
 *
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunAssetDistributionConditionsArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunAssetDistributionConditionsArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			switch(get_class($obj))
			{
				case 'vAssetDistributionPropertyCondition':
					$nObj = new VidiunAssetDistributionPropertyCondition();
					break;
			}

			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}
	
	public function __construct()
	{
		parent::__construct("VidiunAssetDistributionCondition");
	}
}