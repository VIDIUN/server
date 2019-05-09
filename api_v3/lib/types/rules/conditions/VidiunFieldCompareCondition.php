<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunFieldCompareCondition extends VidiunCompareCondition
{
	/**
	 * Field to evaluate
	 * @var VidiunIntegerField
	 */
	public $field;
	 
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = ConditionType::FIELD_COMPARE;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vFieldCompareCondition();
	
		/* @var $dbObject vFieldCompareCondition */
		$dbObject->setField($this->field->toObject());
			
		return parent::toObject($dbObject, $skip);
	}
	 
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject vFieldMatchCondition */
		parent::doFromObject($dbObject, $responseProfile);
		
		$fieldType = get_class($dbObject->getField());
		VidiunLog::debug("Loading VidiunIntegerField from type [$fieldType]");
		switch ($fieldType)
		{
			case 'vTimeContextField':
				$this->field = new VidiunTimeContextField();
				break;
				
			default:
				$this->field = VidiunPluginManager::loadObject('VidiunIntegerField', $fieldType);
				break;
		}
		
		if($this->field)
			$this->field->fromObject($dbObject->getField());
	}
}
