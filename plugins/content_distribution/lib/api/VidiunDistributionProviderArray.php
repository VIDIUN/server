<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunDistributionProviderArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDistributionProviderArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = new VidiunGenericDistributionProvider();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunDistributionProvider");	
	}
}