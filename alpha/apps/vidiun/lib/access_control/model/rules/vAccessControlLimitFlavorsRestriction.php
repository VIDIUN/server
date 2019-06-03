<?php
/**
 * @package Core
 * @subpackage model.data
 * 
 * Old preview restriction for backward compatibility
 */
class vAccessControlLimitFlavorsRestriction extends vAccessControlRestriction
{
	/**
	 * @var vAccessControlLimitFlavorsAction
	 */
	private $action;
	
	/**
	 * @param accessControl $accessControl
	 */
	public function __construct(accessControl $accessControl = null)
	{
		parent::__construct($accessControl);
		
		$this->action = new vAccessControlLimitFlavorsAction();
		$this->setActions(array($this->getAction()));
	}

	/**
	 * @return vAccessControlLimitFlavorsAction
	 */
	private function getAction()
	{
		$actions = $this->getActions();
		if(!$this->action && count($actions))
			$this->action = reset($actions);
			
		return $this->action;
	}
	

	/**
	 * @param string $flavorParamsIds
	 */
	function setFlavorParamsIds($flavorParamsIds)
	{
		$this->getAction()->setFlavorParamsIds($flavorParamsIds);
	}

	/**
	 * @return string
	 */
	function getFlavorParamsIds()
	{
		return $this->getAction()->getFlavorParamsIds();
	}
	
	/**
	 * @return int
	 */
	function getLimitFlavorsRestrictionType()
	{
		return $this->getAction()->getIsBlockedList() ? vAccessControlRestriction::RESTRICTION_TYPE_RESTRICT_LIST : vAccessControlRestriction::RESTRICTION_TYPE_ALLOW_LIST;	
	}
	
	/**
	 * @param int $type
	 */
	function setLimitFlavorsRestrictionType($type)
	{
		$this->getAction()->setIsBlockedList($type == vAccessControlRestriction::RESTRICTION_TYPE_RESTRICT_LIST);
	}

	/* (non-PHPdoc)
	 * @see vRule::shouldDisableCache()
	 */
	public function shouldDisableCache()
	{
		return false;
	}
}
