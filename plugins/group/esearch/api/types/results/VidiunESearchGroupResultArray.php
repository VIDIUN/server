<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchGroupResultArray extends VidiunTypedArray
{
	public function __construct()
	{
		return parent::__construct("VidiunESearchGroupResult");
	}

	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$outputArray = new VidiunESearchGroupResultArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunESearchGroupResult();
			$nObj->fromObject($obj, $responseProfile);
			$outputArray[] = $nObj;
		}
		return $outputArray;
	}

}
