<?php
/**
 * @package plugins.confMaps
 * @subpackage api.filters
 */
class VidiunConfMapsFilter extends VidiunConfMapsBaseFilter
{
	public function getListResponse(VidiunFilterPager $pager, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$response = new VidiunConfMapsListResponse();
		if(!$this->nameEqual || $this->nameEqual=='')
		{
			return $response;
		}
		$items = new VidiunConfMapsArray();

		//Check if map exist in file system or in remote cache
		$remoteCache = vCacheConfFactory::getInstance(vCacheConfFactory::REMOTE_MEM_CACHE);
		$hostList =$remoteCache->getHostList($this->nameEqual ,$this->relatedHostEqual );
		if($hostList)
		{
			foreach ($hostList as $host)
			{
				$dbMapObject = ConfMapsPeer::getMapByVersion($this->nameEqual, $host);
				$apiMapObject = new VidiunConfMaps();
				$apiMapObject->fromObject($dbMapObject);
				$apiMapObject->sourceLocation = VidiunConfMapsSourceLocation::DB;
				$apiMapObject->isEditable = true;
				$items->insert($apiMapObject);
			}
		}
		else		//Check in file system
		{
			$fileSystemCache = vCacheConfFactory::getInstance(vCacheConfFactory::FILE_SYSTEM);
			$fileNames = $fileSystemCache->getIniFilesList($this->nameEqual ,$this->relatedHostEqual);
			foreach ($fileNames as $fileName)
			{
				$mapObject = new VidiunConfMaps();
				list($mapObject->name , $mapObject->relatedHost ,$mapObject->content )  = $fileSystemCache->getMapInfo($fileName);
				$mapObject->sourceLocation = VidiunConfMapsSourceLocation::FS;
				$items->insert($mapObject);
				$mapObject->version = 1;
				$mapObject->isEditable = false;
			}
		}
		$response->objects = $items;
		$response->totalCount = count($items);
		return $response;
	}
	public function getCoreFilter()
	{
		return new ConfMapsFilter();
	}

	/**
	 * @return VidiunConfMaps
	 */
	public function getMap()
	{
		$confMap = new VidiunConfMaps();
		$hostPatern = str_replace('*','#', $this->relatedHostEqual);
		/*  @var vRemoteMemCacheConf $remoteCache  */
		$remoteCache = vCacheConfFactory::getInstance(vCacheConfFactory::REMOTE_MEM_CACHE);
		$map = null;
		if (!is_null($this->versionEqual))
		{
			$dbMap = ConfMapsPeer::getMapByVersion($this->nameEqual, $hostPatern, $this->versionEqual);
			if ($dbMap)
			{
				$confMap->fromObject($dbMap);
				$confMap->sourceLocation = VidiunConfMapsSourceLocation::DB;
				$confMap->isEditable = true;
				return $confMap;
			}
		}
		else
		{
			$map = $remoteCache->loadByHostName($this->nameEqual, $hostPatern);
		}
		if(!empty($map))
		{
			$confMap->sourceLocation = VidiunConfMapsSourceLocation::DB;
			$confMap->isEditable = true;
		}
		else
		{
			/*  @var vFileSystemConf $confFs  */
			$confFs = vCacheConfFactory::getInstance(vCacheConfFactory::FILE_SYSTEM);
			$map = $confFs->loadByHostName($this->nameEqual, $hostPatern);
			$confMap->sourceLocation = VidiunConfMapsSourceLocation::FS;
			$confMap->isEditable = false;
		}
		if(empty($map))
		{
			return null;
		}
		$confMap->name = $this->nameEqual;
		$confMap->content = json_encode($map);

		return $confMap;
	}
}
