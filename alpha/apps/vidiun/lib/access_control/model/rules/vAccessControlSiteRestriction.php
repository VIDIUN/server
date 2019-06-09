<?php
/**
 * @package Core
 * @subpackage model.data
 * 
 * Old site restriction for backward compatibility
 */
class vAccessControlSiteRestriction extends vAccessControlRestriction
{
	/**
	 * @var vSiteCondition
	 */
	private $condition;
	
	/**
	 * @param accessControl $accessControl
	 */
	public function __construct(accessControl $accessControl = null)
	{
		parent::__construct($accessControl);
		$this->setActions(array(new vAccessControlAction(RuleActionType::BLOCK)));
		
		$this->condition = new vSiteCondition(true);
		if($accessControl)
		{
			$this->setSiteList($accessControl->getSiteRestrictList());
			$this->setSiteRestrictionType($accessControl->getSiteRestrictType());
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
			$context->setIsSiteRestricted(true);
			
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
	public function setSiteRestrictionType($type)
	{
		$this->getCondition()->setNot($type == vAccessControlRestriction::RESTRICTION_TYPE_ALLOW_LIST);
	}
	
	/**
	 * @param string $siteList
	 */
	public function setSiteList($values)
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
	public function getSiteRestrictionType()
	{
		return $this->getCondition()->getNot() ? vAccessControlRestriction::RESTRICTION_TYPE_ALLOW_LIST : vAccessControlRestriction::RESTRICTION_TYPE_RESTRICT_LIST;	
	}
	
	/**
	 * @return string
	 */
	public function getSiteList()
	{
		return implode(',', $this->getCondition()->getStringValues());
	}
}
