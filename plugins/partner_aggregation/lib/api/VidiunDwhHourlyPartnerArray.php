<?php
/**
 * @package plugins.partnerAggregation
 * @subpackage api.objects
 */
class VidiunDwhHourlyPartnerArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDwhHourlyPartnerArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = new VidiunDwhHourlyPartner();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunDwhHourlyPartner");	
	}
}