<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use VidiunContextTypeHolderArray
 */
class VidiunAccessControlContextTypeHolderArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunAccessControlContextTypeHolderArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $type)
		{
    		$nObj = new VidiunAccessControlContextTypeHolder();
			$nObj->type = $type;
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		parent::__construct("VidiunAccessControlContextTypeHolder");	
	}
}