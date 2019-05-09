<?php
/**
 * @service filesyncImportBatch
 * @package plugins.multiCenters
 * @subpackage api.services
 */
class FileSyncImportBatchService extends VidiunBatchService
{
	const MAX_FILESYNC_ID_PREFIX = 'fileSyncMaxId-dc';
	const LAST_FILESYNC_ID_PREFIX = 'fileSyncLastId-worker';
	const LOCK_KEY_PREFIX = 'fileSyncLock:id=';
	const LOCK_EXPIRY = 36000;
	const MAX_FILESYNCS_PER_CHUNK = 100;
	const MAX_FILESYNC_QUERIES_PER_CALL = 100;
	const MAX_FILESYNC_ID_RANGE = 20000;
	
	protected static function getOriginalFileSync($fileSync)
	{
		$c = new Criteria();
		$c->addAnd(FileSyncPeer::OBJECT_ID, $fileSync->getObjectId());
		$c->addAnd(FileSyncPeer::OBJECT_TYPE, $fileSync->getObjectType());
		$c->addAnd(FileSyncPeer::VERSION, $fileSync->getVersion());
		$c->addAnd(FileSyncPeer::OBJECT_SUB_TYPE, $fileSync->getObjectSubType());
		$c->addAnd(FileSyncPeer::DC, $fileSync->getOriginalDc());
		return FileSyncPeer::doSelectOne($c);
	}
	
	/**
	 * batch lockPendingFileSyncs action locks file syncs for import by the file sync periodic worker
	 *
	 * @action lockPendingFileSyncs
	 * @param VidiunFileSyncFilter $filter
	 * @param int $workerId The id of the file sync import worker 
	 * @param int $sourceDc The id of the DC from which the file syncs should be pulled
	 * @param int $maxCount The maximum number of file syncs that should be returned
	 * @param int $maxSize The maximum total size of file syncs that should be returned, this limit may be exceeded by one file sync
	 * @return VidiunLockFileSyncsResponse
	 */
	function lockPendingFileSyncsAction(VidiunFileSyncFilter $filter, $workerId, $sourceDc, $maxCount, $maxSize = null)
	{
		// need to explicitly disable the cache since this action may not perform any queries
		vApiCache::disableConditionalCache();
		
		// for dual dc deployments, if source dc is not specified, set it to the remote dc 
		if ($sourceDc < 0)
		{
			$sourceDc = 1 - vDataCenterMgr::getCurrentDcId();
		}
		
		// get caches
		$keysCache = vCacheManager::getSingleLayerCache(vCacheManager::CACHE_TYPE_QUERY_CACHE_KEYS);
		if (!$keysCache)
		{
			throw new VidiunAPIException(MultiCentersErrors::GET_KEYS_CACHE_FAILED);
		}
		
		$lockCache = vCacheManager::getSingleLayerCache(vCacheManager::CACHE_TYPE_LOCK_KEYS);
		if (!$lockCache)
		{
			throw new VidiunAPIException(MultiCentersErrors::GET_LOCK_CACHE_FAILED);
		}
		
		// get the max id / last id
		$maxId = $keysCache->get(self::MAX_FILESYNC_ID_PREFIX . $sourceDc);
		if (!$maxId)
		{
			throw new VidiunAPIException(MultiCentersErrors::GET_MAX_FILESYNC_ID_FAILED, $sourceDc);
		}
		
		// Note: reducing slightly the max id, because the ids may arrive out of order in the mysql replication
		$maxId -= 1000;

		$initialLastId = $keysCache->get(self::LAST_FILESYNC_ID_PREFIX . $workerId);
		VidiunLog::info("got lastId [$initialLastId] for worker [$workerId]");
		
		$lastId = $initialLastId ? $initialLastId : $maxId;
								
		// created at less than handled explicitly
		$createdAtLessThanOrEqual = $filter->createdAtLessThanOrEqual;
		$filter->createdAtLessThanOrEqual = null;
		
		// build the criteria
		$fileSyncFilter = new FileSyncFilter();
		$filter->toObject($fileSyncFilter);
		
		$baseCriteria = new Criteria();
		$fileSyncFilter->attachToCriteria($baseCriteria);
		
		$baseCriteria->add(FileSyncPeer::STATUS, FileSync::FILE_SYNC_STATUS_PENDING);
		$baseCriteria->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_FILE);
		$baseCriteria->add(FileSyncPeer::DC, vDataCenterMgr::getCurrentDcId());
		
		$baseCriteria->addAscendingOrderByColumn(FileSyncPeer::ID);

		$chunkSize = self::MAX_FILESYNCS_PER_CHUNK;
		$baseCriteria->setLimit($chunkSize);
		
		$lockedFileSyncs = array();
		$lockedFileSyncsSize = 0;
		$limitReached = false;
		$selectCount = 0;
		$done = false;
		
		while (!$done && 
				$selectCount < self::MAX_FILESYNC_QUERIES_PER_CALL && 
				$lastId + 100 < $maxId)
		{
			// clear the instance pool every once in a while (not clearing every time since 
			//	some objects repeat between selects)
			$selectCount++;
			if ($selectCount % 5 == 0)
			{
				FileSyncPeer::clearInstancePool();
			}
			
			$idLimit = min($lastId + self::MAX_FILESYNC_ID_RANGE, $maxId);
			// get a chunk of file syncs
			// Note: starting slightly before the last id, because the ids may arrive out of order in the mysql replication
			$c = clone $baseCriteria;
			$idCriterion = $c->getNewCriterion(FileSyncPeer::ID, $lastId, Criteria::GREATER_EQUAL);
			$idCriterion->addAnd($c->getNewCriterion(FileSyncPeer::ID, $idLimit, Criteria::LESS_THAN));
			$c->addAnd($idCriterion);

			// Note: disabling the criteria because it accumulates more and more criterions, and the status was already explicitly added
			//		once that bug is fixed, this can be removed
			FileSyncPeer::setUseCriteriaFilter(false);
			$fileSyncs = FileSyncPeer::doSelect($c, myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_PROPEL2));
			FileSyncPeer::setUseCriteriaFilter(true);

			// if we got less than the limit no reason to perform any more queries
			if (count($fileSyncs) < $chunkSize)
			{
				$lastId = $idLimit;
			}
			else
			{
				$lastFileSync = end($fileSyncs);
				$lastId = $lastFileSync->getId() + 1;
			}

			// filter by source dc
			foreach ($fileSyncs as $index => $fileSync)
			{
				if ($fileSync->getOriginalDc() != $sourceDc)
				{
					unset($fileSyncs[$index]);
				}
			}

			// filter by object type / sub type
			$fileSyncs = array_filter($fileSyncs, array('vFileSyncUtils', 'shouldSyncFileObjectType'));

			// filter by created at
			if ($createdAtLessThanOrEqual)
			{
				foreach ($fileSyncs as $index => $fileSync)
				{
					if ($fileSync->getCreatedAt(null) > $createdAtLessThanOrEqual)
					{
						$done = true;
						unset($fileSyncs[$index]);
						$lastId = min($lastId, $fileSync->getId());
					}
				}
			}

			if (!$fileSyncs)
			{
				continue;
			}

			// get locked file syncs with multi get
			$lockKeys = array();
			foreach ($fileSyncs as $fileSync)
			{
				$lockKeys[] = self::LOCK_KEY_PREFIX . $fileSync->getId();
			}
			
			$lockKeys = $lockCache->get($lockKeys);
			
			// try to lock file syncs
			foreach ($fileSyncs as $fileSync)
			{
				$curKey = self::LOCK_KEY_PREFIX . $fileSync->getId();
				if (isset($lockKeys[$curKey]))
				{
					VidiunLog::info('file sync '.$fileSync->getId().' already locked');
					continue;
				}
				
				if (!$lockCache->add($curKey, true, self::LOCK_EXPIRY))
				{
					VidiunLog::info('failed to lock file sync '.$fileSync->getId());
					continue;
				}
				
				VidiunLog::info('locked file sync ' . $fileSync->getId());
				
				// get the original id if not set
				if (!$fileSync->getOriginalId())
				{
					$originalFileSync = self::getOriginalFileSync($fileSync);
					if (!$originalFileSync)
					{
						VidiunLog::info('failed to get original file sync for '.$fileSync->getId());
						continue;
					}
					
					$fileSync->setOriginalId($originalFileSync->getId());
					$fileSync->setCustomDataObj();	// update $fileSync->custom_data so that originalId will be set by fromObject
				}
				
				// add to the result set
				$lockedFileSyncs[] = $fileSync;
				$lockedFileSyncsSize += $fileSync->getFileSize();
				
				if (count($lockedFileSyncs) >= $maxCount ||
					($maxSize && $lockedFileSyncsSize >= $maxSize))
				{
					$lastId = min($lastId, $fileSync->getId() + 1);
					$limitReached = true;
					$done = true;
					break;
				}
			}
		}
		
		// update the last id
		// Note: it is possible that the last id will go back in case of race condition,
		//		but the only effect of this is that some file syncs will be scanned again		
		if (!$initialLastId || $lastId > $initialLastId)
		{
			VidiunLog::info("setting lastId to [$lastId] for worker [$workerId]");
			
			$keysCache->set(self::LAST_FILESYNC_ID_PREFIX . $workerId, $lastId);
		}
		
		// make sure all file syncs have a path
		foreach ($lockedFileSyncs as $fileSync)
		{
			if ($fileSync->getFileRoot() && $fileSync->getFilePath()) 
			{
				continue;
			}

			$fileSyncKey = vFileSyncUtils::getKeyForFileSync($fileSync);
			list($fileRoot, $realPath) = vPathManager::getFilePathArr($fileSyncKey);

			$fileSync->setFileRoot($fileRoot);
			$fileSync->setFilePath($realPath);
		}
		
		// build the response object
		$sourceDc = vDataCenterMgr::getDcById($sourceDc);
		$result = new VidiunLockFileSyncsResponse;
		$result->fileSyncs = VidiunFileSyncArray::fromDbArray($lockedFileSyncs, $this->getResponseProfile());
		$result->limitReached = $limitReached;
		$result->dcSecret = $sourceDc["secret"];
		$result->baseUrl = isset($sourceDc["fileSyncImportUrl"]) ? $sourceDc["fileSyncImportUrl"] : $sourceDc["url"];
		
		return $result;
	}	

	/**
	 * batch extendFileSyncLock action extends the expiration of a file sync lock
	 *
	 * @action extendFileSyncLock
	 * @param int $id The id of the file sync 
	 */
	function extendFileSyncLockAction($id)
	{
		// need to explicitly disable the cache since this action does not perform any queries
		vApiCache::disableConditionalCache();
		
		$lockCache = vCacheManager::getSingleLayerCache(vCacheManager::CACHE_TYPE_LOCK_KEYS);
		if (!$lockCache)
		{
			throw new VidiunAPIException(MultiCentersErrors::GET_LOCK_CACHE_FAILED);
		}
		
		if (!$lockCache->set(self::LOCK_KEY_PREFIX . $id, true, self::LOCK_EXPIRY))
		{
			throw new VidiunAPIException(MultiCentersErrors::EXTEND_FILESYNC_LOCK_FAILED);
		}
	}
}
