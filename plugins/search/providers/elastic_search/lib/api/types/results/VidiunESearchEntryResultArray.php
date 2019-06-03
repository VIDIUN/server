<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchEntryResultArray extends VidiunTypedArray
{
    public function __construct()
    {
        return parent::__construct("VidiunESearchEntryResult");
    }

	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$outputArray = new VidiunESearchEntryResultArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunESearchEntryResult();
			$nObj->fromObject($obj, $responseProfile);
			$outputArray[] = $nObj;
		}
		return $outputArray;
	}

}
