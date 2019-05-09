<?php
/**
 * @package plugins.eventNotification
 * @subpackage model.data
 */
class vEventFieldCondition extends vCondition
{
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(EventNotificationPlugin::getConditionTypeCoreValue(EventNotificationConditionType::EVENT_NOTIFICATION_FIELD));
		parent::__construct($not);
	}

	/**
	 * Needed in order to migrate old vEventFieldCondition that serialized before vCondition defined as parent class
	 */
	public function __wakeup()
	{
		$this->setType(EventNotificationPlugin::getConditionTypeCoreValue(EventNotificationConditionType::EVENT_NOTIFICATION_FIELD));
	}
	
	/**
	 * The field to evaluate against the values
	 * @var vBooleanField
	 */
	private $field;

	/* (non-PHPdoc)
	 * @see vCondition::internalFulfilled()
	 */
	protected function internalFulfilled(vScope $scope)
	{
		$this->field->setScope($scope);
		return $this->field->getValue();
	}
	
	/**
	 * @return vBooleanField
	 */
	public function getField() 
	{
		return $this->field;
	}

	/**
	 * @param vBooleanField $field
	 */
	public function setField(vBooleanField $field) 
	{
		$this->field = $field;
	}
}
