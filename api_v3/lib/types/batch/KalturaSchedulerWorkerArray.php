<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunSchedulerWorkerArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunSchedulerWorkerArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunSchedulerWorker();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public static function statusFromSchedulerWorkerArray( $arr )
	{
		$newArr = new VidiunSchedulerWorkerArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunSchedulerWorker();
			$nObj->statusFromObject($obj);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunSchedulerWorker" );
	}
}
