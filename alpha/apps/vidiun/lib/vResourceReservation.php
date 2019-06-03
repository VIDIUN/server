<?php

class vResourceReservation
{
	/**
	 * @var ResourceReservation $resourceReservator
	 */
	private $resourceReservator;

	function __construct($ttl = null)
	{
		$cache = vCacheManager::getSingleLayerCache(vCacheManager::CACHE_TYPE_RESOURCE_RESERVATION);
		$vs = vCurrentContext::$vs;
		if (!$ttl)
			$ttl = vConf::get('ResourceReservationDuration');
		$this->resourceReservator = new ResourceReservation($cache, $ttl, $vs);
	}


	/**
	 * will reserve the resource for some time
	 * @param string $resourceId
	 *
	 * @return bool - true if reserve and false if could not
	 */
	public function reserve($resourceId)
	{
		if ($this->resourceReservator->reserve($resourceId))
		{
			VidiunLog::info("Resource reservation was done successfully for resource id [$resourceId]");
			return true;
		}
		VidiunLog::info("Could not reserve resource id [$resourceId]");
		return false;
	}

	/**
	 * will delete the reservation of the resource from cache
	 * @param string $resourceId
	 *
	 * @return bool - true if reserve and false if could not
	 */
	public function deleteReservation($resourceId)
	{
		if ($this->resourceReservator->deleteReservation($resourceId))
		{
			VidiunLog::info("Resource reservation was deleted successfully for resource id [$resourceId]");
			return true;
		}
		VidiunLog::info("Could not delete reservation for resource id [$resourceId]");
		return false;
	}

	/**
	 * will return BatchJob objects.
	 * @param string $resourceId
	 *
	 * @return bool - true mean the resource is available
	 */
	public function checkAvailable($resourceId)
	{
		if ($this->resourceReservator->checkAvailable($resourceId))
		{
			VidiunLog::info("Resource id [$resourceId] is available for usage");
			return true;
		}
		VidiunLog::info("Can not use resource id [$resourceId] - it is reserved");
		return false;
	}
}