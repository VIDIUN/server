<?php

/**
 * @package server-infra
 * @subpackage cache
 */
class vApiCacheWatcher extends vApiCacheBase
{
	public function __construct()
	{
		parent::__construct();
	}

	public function stop()
	{
		$this->removeFromActiveList();
	}
	
	public function apply()
	{
		if ($this->_cacheStatus == self::CACHE_STATUS_DISABLED)
		{
			vApiCache::disableCache();
			return;
		}

		// common cache fields
		foreach ($this->_extraFields as $extraField)
		{
			call_user_func_array(array('vApiCache', 'addExtraField'), $extraField);
		}
		
		// anonymous cache fields
		if ($this->_expiry)
			vApiCache::setExpiry($this->_expiry);
		
		if ($this->_cacheStatus == self::CACHE_STATUS_ANONYMOUS_ONLY)
		{
			vApiCache::disableConditionalCache();
			return;
		}
		
		// conditional cache fields
		if ($this->_conditionalCacheExpiry)
			vApiCache::setConditionalCacheExpiry($this->_conditionalCacheExpiry);
		
		vApiCache::addInvalidationKeys(array_keys($this->_invalidationKeys), $this->_invalidationTime);

		vApiCache::addSqlQueryConditions($this->_sqlConditions);
	}
}
