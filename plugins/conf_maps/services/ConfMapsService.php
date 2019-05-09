<?php
/**
 * @service confMaps
 * @package plugins.confMaps
 * @subpackage api.services
 */
class ConfMapsService extends VidiunBaseService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$vuser = vCurrentContext::getCurrentVsVuser();
		if(!$vuser)
		{
			throw new VidiunAPIException(VidiunErrors::USER_ID_NOT_PROVIDED_OR_EMPTY);
		}
	}

	/**
	 * Add configuration map
	 *
	 * @action add
	 * @param VidiunConfMaps $map
	 * @return VidiunConfMaps
	 * @throws VidiunErrors::MAP_ALREADY_EXIST
	 */
	function addAction(VidiunConfMaps $map)
	{
		$dbMap = ConfMapsPeer::getMapByVersion($map->name, $map->relatedHost);
		if($dbMap)
		{
			throw new VidiunAPIException(VidiunErrors::MAP_ALREADY_EXIST, $map->name, $map->relatedHost);
		}
		$map->validateContent();
		$newMapVersion = new ConfMaps();
		$map->toInsertableObject($newMapVersion);
		$newMapVersion->setStatus(ConfMapsStatus::STATUS_ENABLED);
		$newMapVersion->setVersion(0);
		$newMapVersion->setRemarks(vCurrentContext::$vs);
		$newMapVersion->save();
		$newMapVersion->syncMapsToCache();
		$map->fromObject($newMapVersion);
		return $map;
	}
	/**
	 * Update configuration map
	 *
	 * @action update
	 * @param VidiunConfMaps $map
	 * @return VidiunConfMaps
	 * @throws VidiunErrors::MAP_DOES_NOT_EXIST
	 */
	function updateAction(VidiunConfMaps $map)
	{
		//get map by values name / hostname
		$dbMap = ConfMapsPeer::getMapByVersion($map->name, $map->relatedHost);
		if(!$dbMap)
		{
			throw new VidiunAPIException(VidiunErrors::MAP_DOES_NOT_EXIST );
		}
		$map->validateContent();
		$newMapVersion = new ConfMaps();
		$newMapVersion->addNewMapVersion($dbMap, $map->content);
		$newMapVersion->syncMapsToCache();
		$map->fromObject($newMapVersion);
		return $map;
	}

	/**
	 * List configuration maps
	 *
	 * @action list
	 * @param VidiunConfMapsFilter $filter
	 * @return VidiunConfMapsListResponse
	 * @throws VidiunErrors::MISSING_MAP_NAME
	 */
	function listAction(VidiunConfMapsFilter $filter)
	{
		vApiCache::disableCache();
		$pager = new VidiunFilterPager();
		$response = $filter->getListResponse($pager);
		return $response;
	}

	/**
	 * Get configuration map
	 *
	 * @action get
	 * @param VidiunConfMapsFilter $filter
	 * @return VidiunConfMaps
	 */
	function getAction(VidiunConfMapsFilter $filter)
	{
		vApiCache::disableCache();
		$confMap = $filter->getMap();
		return $confMap;
	}

	/**
	* List configuration maps names
	*
	* @action getMapNames
	* @return VidiunStringArray
	*/
	public function getMapNamesAction()
	{
		$mapNames= ConfMapsPeer::retrieveMapsNames();
		$result =  VidiunStringArray::fromDbArray($mapNames);
		return $result;
	}
}

