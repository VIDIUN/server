<?php

class vDropFolderAllocator
{
	CONST TIME_IN_CACHE_FOR_LOCK = 5;

	/**
	 * will return cache-key for tag
	 * @param string $tag
	 * @return string
	 */
	private static function getCacheKeyForDropFolderTag($tag)
	{
		return "drop_folder_list_key_$tag";
	}
	

	/**
	 * will return cache-key for index by tag
	 * @param string $tag
	 * @return string
	 */
	private static function getCacheKeyForIndex($tag)
	{
		return "drop_folder_list_$tag-index";
	}

	/**
	 * will return cache-key for lock by tag
	 * @param string $tag
	 * @return string
	 */
	private static function getCacheKeyForDBLock($tag)
	{
		return "drop_folder_update_$tag-Lock";
	}

	/**
	 * will return cache-key for lock by dropFolderId
	 * @param int $dropFolderId
	 * @return string
	 */
	private static function getLockKeyForDropFolder($dropFolderId)
	{
		return "dropFolderLock_$dropFolderId";
	}

	/**
	 * @param string $tag
	 * @param int $maxTimeForWatch
	 *
	 * @return DropFolder or null
	 */
	public static function getDropFolder($tag, $maxTimeForWatch = 600)
	{
		$cache = vCacheManager::getSingleLayerCache(vCacheManager::CACHE_TYPE_BATCH_JOBS);
		vApiCache::disableConditionalCache();
		
		if(!$cache)
		{
			VidiunLog::err("Cache layer [" . vCacheManager::CACHE_TYPE_BATCH_JOBS . "] not found, drop folder will not be allocated");
			return null;
		}
			
		$tagKey = self::getCacheKeyForDropFolderTag($tag);
		$dropFolders = $cache->get($tagKey);
		if (!$dropFolders || empty($dropFolders))
			$dropFolders = self::refreshDropFolderListFromDB($cache, $tag);

		if ($dropFolders)
		{
			$indexKey = self::getCacheKeyForIndex($tag);
			$allocateDropFolder = self::allocateDropFolderFromList($cache, $dropFolders, $indexKey,  $maxTimeForWatch);
			if ($allocateDropFolder)
			{
				$dropFolderId = $allocateDropFolder->getId();
				VidiunLog::debug("Allocate drop folder [$dropFolderId] for [$maxTimeForWatch] seconds with tag [$tag]");
				return $allocateDropFolder;
			}
		}
		
		return null;
	}

	/**
	 * free drop folder lock
	 * @param string $dropFolderId
	 */
	public static function freeDropFolder($dropFolderId)
	{
		$lock = vLock::create(self::getLockKeyForDropFolder($dropFolderId));
		$lock->unlock();
	}

	/**
	 * try to lock the given drop folder
	 * @param string $dropFolderId
	 * @param int $maxTimeForWatch
	 *
	 * @return boolean
	 */
	private static function lockDropFolder($dropFolderId, $maxTimeForWatch)
	{
		$lock = vLock::create(self::getLockKeyForDropFolder($dropFolderId));
		return $lock->lock(1, $maxTimeForWatch); // 1 is the lockGrabTimeout (seconds)
	}


	/**
	 * will return DropFolder from cache if exist
	 * @param vBaseCacheWrapper $cache
	 * @param array $dropFolders - of drop folders
	 * @param string $indexKey
	 * @param int $maxTimeForWatch
	 *
	 * @return DropFolder
	 */
	private static function allocateDropFolderFromList($cache, $dropFolders, $indexKey, $maxTimeForWatch)
	{
		if (!$dropFolders || empty($dropFolders))
			return null;

		$numOfDropFolders = count($dropFolders);
		for ($i = 0; $i < $numOfDropFolders; $i++)
		{
			$index = ($cache->increment($indexKey)) % $numOfDropFolders;
			$dropFolderToAllocate = $dropFolders[$index];
			if (self::lockDropFolder($dropFolderToAllocate->getId(), $maxTimeForWatch))
				return $dropFolderToAllocate;
		}
		VidiunLog::debug("Could not allocate any drop folder after [$numOfDropFolders] attempts");
		return null;
	}


	/**
	 * will  insert bulk of Drop folders to the cache from DB
	 * @param vBaseCacheWrapper $cache
	 * @param string $tag
	 *
	 * @return array of DropFolder or null
	 */
	private static function refreshDropFolderListFromDB($cache, $tag)
	{
		$tagLockKey = self::getCacheKeyForDBLock($tag);
		if (!$cache->add($tagLockKey, true, self::TIME_IN_CACHE_FOR_LOCK))
			return null;

		$tagKey = self::getCacheKeyForDropFolderTag($tag);
		$indexKey = self::getCacheKeyForIndex($tag);
		$ttlForList = vConf::get("DropFolderListTTL");

		$dropFoldersFromDB = DropFolderPeer::retrieveByTag($tag, true);
		$numOfFolderFromDB = count($dropFoldersFromDB);
		VidiunLog::info("Got $numOfFolderFromDB drop folder to insert to cache with tag [$tag] for [$ttlForList] seconds");

		$indexKeyTtl = 86400 * 14; // day in secods * 14 days
		$cache->add($indexKey, 0, $indexKeyTtl);
		$cache->set($tagKey, $dropFoldersFromDB, $ttlForList);

		$cache->delete($tagLockKey);
		return $dropFoldersFromDB;
	}

}
