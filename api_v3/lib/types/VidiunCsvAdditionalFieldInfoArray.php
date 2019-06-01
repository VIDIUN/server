<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunCsvAdditionalFieldInfoArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunCsvAdditionalFieldInfoArray();
		foreach($arr as $obj)
		{
			$nObj = new VidiunCsvAdditionalFieldInfo();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}

	public function __construct()
	{
		return parent::__construct("VidiunCsvAdditionalFieldInfo");
	}
}