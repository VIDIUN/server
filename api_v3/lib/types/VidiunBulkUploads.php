<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBulkUploads extends VidiunTypedArray
{
	public static function fromBatchJobArray ($arr)
	{
		$newArr = new VidiunBulkUploads();
		if ($arr == null)
			return $newArr;
					
		foreach ($arr as $obj)
		{
			$nObj = new VidiunBulkUpload();
			$nObj->fromObject($obj);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunBulkUpload");	
	}
}