<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunPermissionItemArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunPermissionItemArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			if ($obj->getType() == PermissionItemType::API_ACTION_ITEM) {
				$nObj = new VidiunApiActionPermissionItem();
			}
			else if ($obj->getType() == PermissionItemType::API_PARAMETER_ITEM) {
				$nObj = new VidiunApiParameterPermissionItem();
			}
			else {
				VidiunLog::crit('Unknown permission item type ['.$obj->getType().'] defined with id ['.$obj->getId().'] - skipping!');
				continue;
			}
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct('VidiunPermissionItem');	
	}
}
