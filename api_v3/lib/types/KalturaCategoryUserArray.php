<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunCategoryUserArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunCategoryUserArray();
		foreach($arr as $obj)
		{
			$nObj = new VidiunCategoryUser();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunCategoryUser");
	}
}