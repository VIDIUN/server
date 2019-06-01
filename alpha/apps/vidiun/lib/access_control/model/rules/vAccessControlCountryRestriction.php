<?php
/**
 * @package Core
 * @subpackage model.data
 * 
 * Old country restriction for backward compatibility
 */
class vAccessControlCountryRestriction extends vAccessControlRestriction
{
	/**
	 * @var vCountryCondition
	 */
	private $condition;
	
	/**
	 * @param accessControl $accessControl
	 */
	public function __construct(accessControl $accessControl = null)
	{
		parent::__construct($accessControl);
		$this->setActions(array(new vAccessControlAction(RuleActionType::BLOCK)));
		
		$this->condition = new vCountryCondition(true);
		if($accessControl)
		{
			$this->setCountryList($accessControl->getCountryRestrictList());
			$this->setCountryRestrictionType($accessControl->getCountryRestrictType());
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
			$context->setIsCountryRestricted(true);
			
		return $fulfilled;
	}

	/**
	 * @return vCountryCondition
	 */
	protected function getCondition()
	{
		$conditions = $this->getConditions();
		if(!$this->condition && count($conditions))
			$this->condition = reset($conditions);
			
		return $this->condition;
	}

	/**
	 * @param int $type
	 */
	function setCountryRestrictionType($type)
	{
		$this->getCondition()->setNot($type == vAccessControlRestriction::RESTRICTION_TYPE_ALLOW_LIST);
	}
	
	/**
	 * @param string $countryList
	 */
	function setCountryList($values)
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
	function getCountryRestrictionType()
	{
		return $this->getCondition()->getNot() ? vAccessControlRestriction::RESTRICTION_TYPE_ALLOW_LIST : vAccessControlRestriction::RESTRICTION_TYPE_RESTRICT_LIST;	
	}
	
	/**
	 * @return string
	 */
	function getCountryList()
	{
		return implode(',', $this->getCondition()->getStringValues());
	}
}

