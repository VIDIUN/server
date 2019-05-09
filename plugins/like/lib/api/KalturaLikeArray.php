<?php
/**
 * @package plugins.like
 * @subpackage api.objects
 */
class VidiunLikeArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunLikeArray();
		if ($arr == null)
			return $newArr;
	
		foreach ($arr as $obj)
		{
			$nObj = new VidiunLike();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunLike");	
	}
}
