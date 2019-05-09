<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBatchJobArray extends VidiunTypedArray
{
	public static function fromStatisticsBatchJobArray ( $arr )
	{
		$newArr = new VidiunBatchJobArray();
		if ( is_array ( $arr ) )
		{
			foreach ( $arr as $obj )
			{
				$nObj = new VidiunBatchJob();
				$nObj->fromStatisticsObject($obj);
				$newArr[] = $nObj;
			}
		}
		
		return $newArr;
	}
	
	public static function fromBatchJobArray ( $arr )
	{
		$newArr = new VidiunBatchJobArray();
		if ( is_array ( $arr ) )
		{
			foreach ( $arr as $obj )
			{
				$nObj = new VidiunBatchJob();
				$nObj->fromBatchJob($obj);
				$newArr[] = $nObj;
			}
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunBatchJob" );
	}
}
?>