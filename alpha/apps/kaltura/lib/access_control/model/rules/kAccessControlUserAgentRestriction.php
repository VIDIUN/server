<?php
/**
 * @package Core
 * @subpackage model.data
 * 
 * Old user agent address restriction for backward compatibility
 */
class vAccessControlUserAgentRestriction extends vAccessControlRestriction
{
	/**
	 * @var vUserAgentCondition
	 */
	private $condition;
	
	/**
	 * @param accessControl $accessControl
	 */
	public function __construct(accessControl $accessControl = null)
	{
		parent::__construct($accessControl);
		$this->setActions(array(new vAccessControlAction(RuleActionType::BLOCK)));
		
		$this->condition = new vUserAgentCondition(true);
		if($accessControl)
		{
			$strArray = unserialize($accessControl->getFromCustomData(accessControl::USER_AGENT_RESTRICTION_COLUMN_NAME));
			$this->setUserAgentRestrictionType($strArray['type']);
			$this->setUserAgentRegexList($strArray['userAgentRegexList']);
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
			$context->setIsUserAgentRestricted(true);
			
		return $fulfilled;
	}

	/**
	 * @return vUserAgentCondition
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
	function setUserAgentRestrictionType($type)
	{
		$this->getCondition()->setNot($type == vAccessControlRestriction::RESTRICTION_TYPE_ALLOW_LIST);
	}
	
	/**
	 * @param string $userAgentRegexList
	 */
	function setUserAgentRegexList($values)
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
	function getUserAgentRestrictionType()
	{
		return $this->getCondition()->getNot() ? vAccessControlRestriction::RESTRICTION_TYPE_ALLOW_LIST : vAccessControlRestriction::RESTRICTION_TYPE_RESTRICT_LIST;	
	}
	
	/**
	 * @return string
	 */
	function getUserAgentRegexList()
	{
		return implode(',', $this->getCondition()->getStringValues());
	}
}

