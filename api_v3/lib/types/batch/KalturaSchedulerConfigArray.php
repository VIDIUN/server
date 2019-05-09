<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunSchedulerConfigArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunSchedulerConfigArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunSchedulerConfig();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunSchedulerConfig" );
	}
}
