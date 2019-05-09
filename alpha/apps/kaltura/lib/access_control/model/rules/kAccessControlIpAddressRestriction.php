<?php
/**
 * @package Core
 * @subpackage model.data
 * 
 * Old IP address restriction for backward compatibility
 */
class vAccessControlIpAddressRestriction extends vAccessControlRestriction
{
	/**
	 * @var vIpAddressCondition
	 */
	private $condition;
	
	/**
	 * @param accessControl $accessControl
	 */
	public function __construct(accessControl $accessControl = null)
	{
		parent::__construct($accessControl);
		$this->setActions(array(new vAccessControlAction(RuleActionType::BLOCK)));
		
		$this->condition = new vIpAddressCondition(true);
		if($accessControl)
		{
			$strArray = unserialize($accessControl->getFromCustomData(accessControl::IP_ADDRESS_RESTRICTION_COLUMN_NAME));
			$this->setIpAddressRestrictionType($strArray['type']);
			$this->setIpAddressList($strArray['ipAddressList']);
		}
		
		$this->setConditions(array($this->getCondition()));
	}

	/* (non-PHPdoc)
	 * @see vRule::applyContext()
	 */
	public function applyContext(vContextDataResult $context)
	{
		$fulfilled = parent::applyContext($context);
		if($fulfilled)
			$context->setIsIpAddressRestricted(true);
			
		return $fulfilled;
	}

	/**
	 * @return vSiteCondition
	 */
	private function getCondition()
	{
		$conditions = $this->getConditions();
		if(!$this->condition && count($conditions))
			$this->condition = reset($conditions);
			
		return $this->condition;
	}

	/**
	 * @param int $type
	 */
	function setIpAddressRestrictionType($type)
	{
		$this->getCondition()->setNot($type == vAccessControlRestriction::RESTRICTION_TYPE_ALLOW_LIST);
	}
	
	/**
	 * @param string $ipAddressList
	 */
	function setIpAddressList($values)
	{
		$values = explode(',', $values);
		$stringValues = array();
		foreach($values as $value)
			$stringValues[] = new vStringValue($value);
			
		$this->getCondition()->setValues($stringValues);
	}
	
	/**
	 * @return int
	 */
	function getIpAddressRestrictionType()
	{
		return $this->getCondition()->getNot() ? vAccessControlRestriction::RESTRICTION_TYPE_ALLOW_LIST : vAccessControlRestriction::RESTRICTION_TYPE_RESTRICT_LIST;	
	}
	
	/**
	 * @return string
	 */
	function getIpAddressList()
	{
		return implode(',', $this->getCondition()->getStringValues());
	}
}

