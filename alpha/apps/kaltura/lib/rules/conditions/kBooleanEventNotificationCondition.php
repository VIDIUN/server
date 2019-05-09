<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vBooleanEventNotificationCondition extends vCondition
{
	/**
	 * @var string
	 */
	protected $booleanEventNotificationIds;

	public function __construct($not = false)
	{
		$this->setType(ConditionType::BOOLEAN);
		parent::__construct($not);
	}

	/* (non-PHPdoc)
 	* @see vCondition::internalFulfilled()
 	*/
	protected function internalFulfilled(vScope $scope)
	{
		return true;
	}

	/**
	 * @return string
	 */
	function getBooleanEventNotificationIds()
	{
		return $this->booleanEventNotificationIds;
	}

	/**
	 * @param string
	 */
	function setBooleanEventNotificationIds($booleanEventNotificationIds)
	{
		$this->booleanEventNotificationIds = $booleanEventNotificationIds;
	}

}

