<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDestFileSyncDescriptorArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDestFileSyncDescriptorArray();
		if ($arr == null)
			return $newArr;
		foreach ($arr as $obj)
		{
    		$nObj = new VidiunDestFileSyncDescriptor();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunDestFileSyncDescriptor");	
	}
}
