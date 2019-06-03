<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBulkUploadResultArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunBulkUploadResultArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunBulkUploadResult();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct( )
	{
		return parent::__construct ( "VidiunBulkUploadResult" );
	}
}
