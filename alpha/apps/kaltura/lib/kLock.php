<?php
/**
 * @package Core
 * @subpackage utils
 */
class vLock extends vLockBase
{
	/**
	 * @param string $key
	 * @return NULL|vLock
	 */
	static public function create($key)
	{
		$store = vCacheManager::getSingleLayerCache(vCacheManager::CACHE_TYPE_LOCK_KEYS);
		if (!$store)
			return null;
		
		return new vLock($store, $key);
	}
		
	/**
	 * @param callback $callback
	 * @param array $params
	 * @param float $lockGrabTimeout
	 * @param int $lockHoldTimeout
	 * @return mixed
	 */
	public function runLockedImpl($callback, array $params = array(), $lockGrabTimeout = 2, $lockHoldTimeout = 5)
	{
		if (!$this->lock($lockGrabTimeout, $lockHoldTimeout))
			throw new vCoreException("Timed out grabbing [{$this->key}]", vCoreException::LOCK_TIMED_OUT);
		
		try
		{
			$result = call_user_func_array($callback, $params);
		}
		catch (Exception $e)
		{
			$this->unlock();
			throw $e;
		}
		$this->unlock();
		return $result;			
	}	

	/**
	 * @param string $key
	 * @param callback $callback
	 * @param array $params
	 * @param float $lockGrabTimeout
	 * @param int $lockHoldTimeout
	 * @return mixed
	 */
	static public function runLocked($key, $callback, array $params = array(), $lockGrabTimeout = 2, $lockHoldTimeout = 5)
	{
		$lock = self::create($key);
		if (!$lock)
			return call_user_func_array($callback, $params);
			
		return $lock->runLockedImpl($callback, $params, $lockGrabTimeout, $lockHoldTimeout);
	}	
}
