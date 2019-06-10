<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunSchedulerArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunSchedulerArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunScheduler();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public static function statusFromSchedulerArray( $arr )
	{
		$newArr = new VidiunSchedulerArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunScheduler();
			$nObj->statusFromObject($obj);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunScheduler" );
	}
}
