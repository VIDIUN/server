<?php
/**
 * @package plugins.reach
 * @subpackage api.objects
 */
class VidiunEntryVendorTaskArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunEntryVendorTaskArray();
		if ($arr == null)
			return $newArr;
		
		foreach ($arr as $obj)
		{
			$object = new VidiunEntryVendorTask();
			$object->fromObject($obj, $responseProfile);
			$newArr[] = $object;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		parent::__construct("VidiunEntryVendorTask");
	}
}