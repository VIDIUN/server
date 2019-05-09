<?php
/**
 * @package plugins.tagSearch
 * @subpackage api.objects
 */
class VidiunTagArray extends VidiunTypedArray
{
    /**
     * Function returns an array of API objects for the array of DB 
     * objects it is passed.
     * @param array $arr
     * @return VidiunTagArray
     */
    public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunTagArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = new VidiunTag();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunTag");	
	}
}