<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunReportExportItemArray extends VidiunTypedArray
{

	public function __construct()
	{
		return parent::__construct("VidiunReportExportItem");
	}

	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunReportExportItemArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunReportExportItem();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}

		return $newArr;
	}

}
