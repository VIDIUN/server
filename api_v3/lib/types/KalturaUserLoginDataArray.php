<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUserLoginDataArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunUserLoginDataArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunUserLoginData();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
		 
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunUserLoginData" );
	}
}
