<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUiConfArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunUiConfArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunUiConf();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunUiConf" );
	}
}
