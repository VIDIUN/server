<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vUserAgentCondition extends vRegexCondition
{
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::USER_AGENT);
		parent::__construct($not);
	}
	
	/* (non-PHPdoc)
	 * @see vCondition::getFieldValue()
	 */
	public function getFieldValue(vScope $scope)
	{
		vApiCache::addExtraField(vApiCache::ECF_USER_AGENT, vApiCache::COND_REGEX, $this->getStringValues($scope));
		return $scope->getUserAgent();
	}

	/* (non-PHPdoc)
	 * @see vMatchCondition::shouldFieldDisableCache()
	 */
	public function shouldFieldDisableCache($scope)
	{
		return false;
	}
}
