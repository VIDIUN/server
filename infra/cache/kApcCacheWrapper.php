<?php

require_once(dirname(__FILE__) . '/vInfraBaseCacheWrapper.php');

/**
 * @package infra
 * @subpackage cache
 */
class vApcCacheWrapper extends vInfraBaseCacheWrapper
{
	/* (non-PHPdoc)
	 * @see vBaseCacheWrapper::init()
	 */
	protected function doInit($config)
	{
		if (!function_exists('apc_fetch'))
			return false;
		return true;
	}

	/* (non-PHPdoc)
	 * @see vBaseCacheWrapper::get()
	 */
	protected function doGet($key)
	{
		return apc_fetch($key);
	}
		
	/* (non-PHPdoc)
	 * @see vBaseCacheWrapper::set()
	 */
	protected function doSet($key, $var, $expiry = 0)
	{
		return apc_store($key, $var, $expiry);
	}
	
	/* (non-PHPdoc)
	 * @see vBaseCacheWrapper::add()
	 */
	protected function doAdd($key, $var, $expiry = 0)
	{
		return apc_add($key, $var, $expiry);
	}
	
	/* (non-PHPdoc)
	 * @see vBaseCacheWrapper::multiGet()
	 */
	protected function doMultiGet($keys)
	{
		return apc_fetch($keys);
	}


	/* (non-PHPdoc)
	 * @see vBaseCacheWrapper::delete()
	 */
	protected function doDelete($key)
	{
		return apc_delete($key);
	}
	
	/* (non-PHPdoc)
	 * @see vBaseCacheWrapper::increment()
	 */
	public function doIncrement($key, $delta = 1)
	{
		return apc_inc($key, $delta);
	}
	
	/* (non-PHPdoc)
	 * @see vBaseCacheWrapper::decrement()
	 */
	public function doDecrement($key, $delta = 1)
	{
		return apc_dec($key, $delta);
	}
}
