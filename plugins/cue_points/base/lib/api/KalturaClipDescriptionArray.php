<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunClipDescriptionArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunClipDescriptionArray();
		if ($arr == null)
			return $newArr;
		foreach ($arr as $obj)
		{
    		$nObj = new VidiunClipDescription();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}

	public function __construct()
	{
		parent::__construct("VidiunClipDescription");
	}
}
