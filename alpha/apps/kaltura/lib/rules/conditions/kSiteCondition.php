<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vSiteCondition extends vMatchCondition
{
	/**
	 * Indicates that global whitelist domains already appended 
	 * @var bool
	 */
	private $globalWhitelistDomainsAppended = false;
	
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::SITE);
		parent::__construct($not);
	}
	
	/* (non-PHPdoc)
	 * @see vCondition::getFieldValue()
	 */
	public function getFieldValue(vScope $scope)
	{
		$referrer = $scope->getReferrer();
		return requestUtils::parseUrlHost($referrer);
	}
	
	/* (non-PHPdoc)
	 * @see vCondition::internalFulfilled()
	 */
	protected function internalFulfilled(vScope $scope)
	{
		$referrer = $scope->getReferrer();

		if ($this->getNot()===true && !$this->globalWhitelistDomainsAppended && strpos($referrer, "vwidget") === false && vConf::hasParam("global_whitelisted_domains"))
		{
			$vs = $scope->getVs();
			if (!$vs || !in_array($vs->partner_id, vConf::getMap('global_whitelisted_domains_exclude_list')))
			{
				$this->globalWhitelistDomainsAppended = true;
			
				$globalWhitelistedDomains = vConf::get("global_whitelisted_domains");
				if(!is_array($globalWhitelistedDomains))
					$globalWhitelistedDomains = explode(',', $globalWhitelistedDomains);
				
				foreach($globalWhitelistedDomains as $globalWhitelistedDomain)
					$this->values[] = new vStringValue($globalWhitelistedDomain);
			}
		}

		vApiCache::addExtraField(vApiCache::ECF_REFERRER, vApiCache::COND_SITE_MATCH, $this->getStringValues($scope));
		
		return parent::internalFulfilled($scope);
	}
	
	/* (non-PHPdoc)
	 * @see vMatchCondition::matches()
	 */
	protected function matches($field, $value)
	{
		return ($field === $value) || (strpos($field, ".".$value) !== false);
	}

	/* (non-PHPdoc)
	 * @see vCondition::shouldFieldDisableCache()
	 */
	public function shouldFieldDisableCache($scope)
	{
		return false;
	}
}
