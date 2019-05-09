<?php
/**
 * @package plugins.schedule
 * @subpackage api.objects
 */
class VidiunScheduleEventArray extends VidiunTypedArray
{
	public static function fromDbArray($arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunScheduleEventArray();
		if ($arr == null)
			return $newArr;

		// preload all parents in order to have them in the instance pool
		$parentIds = array();
		foreach ($arr as $obj)
		{
			/* @var $obj ScheduleEvent */
			if($obj->getParentId())
			{
				$parentIds[$obj->getParentId()] = true;
			} 
		}
		if(count($parentIds))
		{
			ScheduleEventPeer::retrieveByPKs(array_keys($parentIds));
		}
		
		foreach ($arr as $obj)
		{
			$newArr[] = VidiunScheduleEvent::getInstance($obj, $responseProfile);
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunScheduleEvent");	
	}
}