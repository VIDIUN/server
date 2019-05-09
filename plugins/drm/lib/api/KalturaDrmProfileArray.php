<?php
/**
 * @package plugins.drm
 * @subpackage api.objects
 */
class VidiunDrmProfileArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDrmProfileArray();
		foreach ( $arr as $obj )
		{
		    $nObj = VidiunDrmProfile::getInstanceByType($obj->getProvider());
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
		 
	}
	
	public function __construct( )
	{
		return parent::__construct ( 'VidiunDrmProfile' );
	}
}
