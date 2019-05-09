<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDetachedResponseProfileArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDetachedResponseProfileArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = new VidiunDetachedResponseProfile();
			if(!$nObj)
			{
				VidiunLog::alert("Object [" . get_class($obj) . "] type [" . $obj->getType() . "] could not be translated to API object");
				continue;
			}
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}

	public function __construct()
	{
		parent::__construct("VidiunDetachedResponseProfile");	
	}
}