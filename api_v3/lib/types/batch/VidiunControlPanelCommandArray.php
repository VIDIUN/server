<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunControlPanelCommandArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunControlPanelCommandArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunControlPanelCommand();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunControlPanelCommand" );
	}
}
