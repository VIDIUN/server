<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunCategoryArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunCategoryArray();
		foreach($arr as $obj)
		{
			$nObj = new VidiunCategory();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunCategory");
	}
}
?>