<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunSourceFileSyncDescriptorArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunSourceFileSyncDescriptorArray();
		if ($arr == null)
			return $newArr;
		foreach ($arr as $obj)
		{
    		$nObj = new VidiunSourceFileSyncDescriptor();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunSourceFileSyncDescriptor");	
	}
}
