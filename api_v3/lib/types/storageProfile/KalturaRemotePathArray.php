<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunRemotePathArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr = null, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunRemotePathArray();
		foreach($arr as $obj)
		{
			$nObj = new VidiunRemotePath();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunRemotePath" );
	}
}
