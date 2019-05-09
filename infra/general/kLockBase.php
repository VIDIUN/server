<?php
/**
 * @package Core
 * @subpackage utils
 */
class vLockBase
{
	const LOCK_KEY_PREFIX = '__LOCK';
	const LOCK_GRAB_TRY_INTERVAL = 20000;
	
	/**
	 * @var vBaseCacheWrapper
	 */
	protected $store;
	
	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @param vBaseCacheWrapper $store
	 * @param string $key
	 */
	public function __construct($store, $key)
	{
		$this->store = $store;
		$this->key = self::LOCK_KEY_PREFIX . $key;
	}
	
	/**
	 * @param float $lockGrabTimeout
	 * @param int $lockHoldTimeout
	 * @return boolean
	 */
	public function lock($lockGrabTimeout = 2, $lockHoldTimeout = 5)
	{
		self::safeLog("Grabbing lock [{$this->key}]");

		$retryTimeout = microtime(true) + $lockGrabTimeout;
		while (microtime(true) < $retryTimeout)
		{
			if (!$this->store->add($this->key, true, $lockHoldTimeout))
			{
				usleep(self::LOCK_GRAB_TRY_INTERVAL);
				continue;
			}
			
			self::safeLog("Lock grabbed [{$this->key}]");
			return true;
		}

		self::safeLog("Lock grab timed out [{$this->key}]");
		return false;
	}
	
	public function unlock()
	{
		self::safeLog("Releasing lock [{$this->key}]");
		if ($this->store->delete($this->key))
		{
			self::safeLog("Lock released [{$this->key}]");
			return true;
		}

		self::safeLog("Lock release failed for [{$this->key}]");
		return false;
	}

	/**
	 * This function is required since this code can run before the autoloader
	 *
	 * @param string $msg
	 */
	protected static function safeLog($msg)
	{
		if (class_exists('VidiunLog'))
			VidiunLog::log($msg);
	}

	/**
	 * @param string $key
	 * @return vLockBase
	 */
	static public function grabLocalLock($key)
	{ 
		if (!function_exists('apc_add'))
			return null;
		
		require_once(__DIR__ . '/../cache/vApcCacheWrapper.php');		// can be called before autoloader
		
		$lock = new vLockBase(new vApcCacheWrapper(), $key);
		if (!$lock->lock())
			return null;
		
		return $lock;
	}
}
