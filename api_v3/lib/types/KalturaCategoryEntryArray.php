<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunCategoryEntryArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunCategoryEntryArray();
		foreach($arr as $obj)
		{
			$nObj = new VidiunCategoryEntry();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunCategoryEntry");
	}
}