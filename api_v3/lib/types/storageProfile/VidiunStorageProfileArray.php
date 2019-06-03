<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunStorageProfileArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunStorageProfileArray();
		foreach($arr as $obj)
		{
		    /* @var $obj StorageProfile */
			$nObj = VidiunStorageProfile::getInstanceByType($obj->getProtocol());
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunStorageProfile" );
	}
}
