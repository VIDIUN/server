<?php
/**
 * @package plugins.annotation
 * @subpackage api.objects
 */
class VidiunAnnotationArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunAnnotationArray();
		if ($arr == null)
			return $newArr;
		
		foreach ($arr as $obj)
		{
    		$nObj = new VidiunAnnotation();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunAnnotation");	
	}
}
