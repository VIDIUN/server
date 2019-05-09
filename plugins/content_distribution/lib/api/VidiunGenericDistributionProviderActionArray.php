<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunGenericDistributionProviderActionArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunGenericDistributionProviderActionArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = new VidiunGenericDistributionProviderAction();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunGenericDistributionProviderAction");	
	}
}