<?php
/**
 * @package plugins.schedule
 * @subpackage api.objects
 */
class VidiunScheduleEventRecurrenceArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunScheduleEventRecurrenceArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = new VidiunScheduleEventRecurrence();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunScheduleEventRecurrence");	
	}
}