<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunWidgetArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunWidgetArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunWidget();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunWidget" );
	}
}
