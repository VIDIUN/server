<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUploadTokenArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunUploadTokenArray();
		foreach($arr as $obj)
		{
			$nObj = new VidiunUploadToken();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunUploadToken");
	}
}
