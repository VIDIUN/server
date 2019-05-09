<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunExtendingItemMrssParameterArray extends VidiunTypedArray
{
	
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunExtendingItemMrssParameterArray();
		foreach($arr as $obj)
		{
			$nObj = new VidiunExtendingItemMrssParameter();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunExtendingItemMrssParameter");
	}
}