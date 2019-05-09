<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchUserResultArray extends VidiunTypedArray
{
    public function __construct()
    {
        return parent::__construct("VidiunESearchUserResult");
    }

	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$outputArray = new VidiunESearchUserResultArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunESearchUserResult();
			$nObj->fromObject($obj, $responseProfile);
			$outputArray[] = $nObj;
		}
		return $outputArray;
	}

}
