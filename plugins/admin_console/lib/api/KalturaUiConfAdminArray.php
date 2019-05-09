<?php
/**
 * @package plugins.adminConsole
 * @subpackage api.objects
 */
class VidiunUiConfAdminArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunUiConfAdminArray();
		foreach ( $arr as $obj )
		{
			$nObj = new VidiunUiConfAdmin();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
	
	public function __construct()
	{
		return parent::__construct("VidiunUiConfAdmin");
	}
}
