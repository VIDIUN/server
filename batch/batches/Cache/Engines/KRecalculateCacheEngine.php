<?php
/**
 * @package Scheduler
 * @subpackage RecalculateCache
 */
abstract class VRecalculateCacheEngine
{
	/**
	 * @param int $objectType of enum VidiunRecalculateCacheType
	 * @return VRecalculateCacheEngine
	 */
	public static function getInstance($objectType)
	{
		switch($objectType)
		{
			case VidiunRecalculateCacheType::RESPONSE_PROFILE:
				return new VRecalculateResponseProfileCacheEngine();
				
			default:
				return VidiunPluginManager::loadObject('VRecalculateCacheEngine', $objectType);
		}
	}
	
	/**
	 * @param VidiunRecalculateCacheJobData $data
	 * @return int cached objects count
	 */
	abstract public function recalculate(VidiunRecalculateCacheJobData $data);
}
