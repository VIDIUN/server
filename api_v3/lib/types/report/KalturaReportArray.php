<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunReportArray extends VidiunTypedArray
{
	public function __construct()
	{
		return parent::__construct("VidiunReport");
	}
	
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunReportArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = new VidiunReport();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
}
?>