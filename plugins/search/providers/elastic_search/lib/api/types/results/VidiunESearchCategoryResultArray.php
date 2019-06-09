<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchCategoryResultArray extends VidiunTypedArray
{
    public function __construct()
    {
        return parent::__construct("VidiunESearchCategoryResult");
    }

	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$outputArray = new VidiunESearchCategoryResultArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunESearchCategoryResult();
			$nObj->fromObject($obj, $responseProfile);
			$outputArray[] = $nObj;
		}
		return $outputArray;
	}
}
