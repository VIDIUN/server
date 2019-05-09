<?php
/**
 * @package plugins.drm
 * @subpackage api.objects
 */
class VidiunDrmPolicyArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDrmPolicyArray();
		foreach ( $arr as $obj )
		{
		    $nObj = VidiunDrmPolicy::getInstanceByType($obj->getProvider());
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
		 
	}
	
	public function __construct( )
	{
		return parent::__construct ( 'VidiunDrmPolicy' );
	}
}
