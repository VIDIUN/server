<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunDistributionProfileArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDistributionProfileArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = VidiunDistributionProfileFactory::createVidiunDistributionProfile($obj->getProviderType());
    		if(!$nObj)
    		{
    			VidiunLog::err("Distribution Profile Factory could not find matching profile type for provider [" . $obj->getProviderType() . "]");
    			continue;
    		}
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunDistributionProfile");	
	}
}