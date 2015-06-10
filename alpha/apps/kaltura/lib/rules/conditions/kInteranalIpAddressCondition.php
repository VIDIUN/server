<?php
/**
 * @package Core
 * @subpackage model.data
 */
class kInteranalIpAddressCondition extends kIpAddressCondition
{
	/* (non-PHPdoc)
	 * @see kCondition::__construct()
	 */
	public function __construct($not = false)
	{
		parent::__construct($not);
		$this->setType(ConditionType::INTERNAL_IP_ADDRESS);
	}
	
	/* (non-PHPdoc)
	 * @see kCondition::getFieldValue()
	 */
	public function getFieldValue(kScope $scope)
	{
		kApiCache::addExtraField(kApiCache::ECF_IP, kApiCache::COND_IP_RANGE, $this->getStringValues($scope));
		return $scope->getIp();	
	}
}
