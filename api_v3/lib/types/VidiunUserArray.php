<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUserArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunUserArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunUser();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
		 
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunUser" );
	}
}
