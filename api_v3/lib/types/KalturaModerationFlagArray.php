<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunModerationFlagArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr = null, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunModerationFlagArray();
		foreach($arr as $obj)
		{
			$nObj = new VidiunModerationFlag();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunModerationFlag");
	}
}
