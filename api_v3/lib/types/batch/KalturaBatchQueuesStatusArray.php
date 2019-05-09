<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBatchQueuesStatusArray extends VidiunTypedArray 
{
	public static function fromBatchQueuesStatusArray($arr)
	{
		$newArr = new VidiunBatchQueuesStatusArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunBatchQueuesStatus();
			
			$nObj->jobType = $obj['JOB_TYPE'];
			$nObj->typeName = BatchJob::getTypeName($nObj->jobType);
			$nObj->size = $obj['JOB_TYPE_COUNT'];
			
			if(isset($obj['CREATED_AT_AVG']))
				$nObj->waitTime = $obj['CREATED_AT_AVG'];
			
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunBatchQueuesStatus" );
	}
}