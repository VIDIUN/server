<?php
/**
 * @package Core
 * @subpackage model.data
 * 
 * Old session restriction for backward compatibility
 */
class vAccessControlSessionRestriction extends vAccessControlRestriction
{
	/**
	 * @var vAuthenticatedCondition
	 */
	private $condition;
	
	/**
	 * @param accessControl $accessControl
	 */
	public function __construct(accessControl $accessControl = null)
	{
		parent::__construct($accessControl);
		$this->setActions(array(new vAccessControlAction(RuleActionType::BLOCK)));
		
		$this->condition = new vAuthenticatedCondition(true);
		if($accessControl)
			$this->condition->setPrivileges(array($accessControl->getVsRestrictPrivilege()));
		
		$this->setConditions(array($this->getCondition()));
	}

	/* (non-PHPdoc)
	 * @see vRule::applyContext()
	 */
	public function applyContext(vContextDataResult $context)
	{
		$fulfilled = parent::applyContext($context);
		if($fulfilled)
			$context->setIsSessionRestricted(true);
			
		return $fulfilled;
	}

	/**
	 * @return vAuthenticatedCondition
	 */
	private function getCondition()
	{
		$conditions = $this->getConditions();
		if(!$this->condition && count($conditions))
			$this->condition = reset($conditions);
			
		return $this->condition;
	}
}

