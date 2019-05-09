<?php
/**
 * @package plugins.schedule
 * @subpackage api.objects
 */
class VidiunScheduleResourceArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunScheduleResourceArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$newArr[] = VidiunScheduleResource::getInstance($obj, $responseProfile);
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunScheduleResource");	
	}
}