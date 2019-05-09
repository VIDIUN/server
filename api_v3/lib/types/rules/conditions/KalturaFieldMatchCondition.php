<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunFieldMatchCondition extends VidiunMatchCondition
{
	/**
	 * Field to evaluate
	 * @var VidiunStringField
	 */
	public $field;
	
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = ConditionType::FIELD_MATCH;
	}
	 
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vFieldMatchCondition();
	
		/* @var $dbObject vFieldMatchCondition */
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
		VidiunLog::debug("Loading VidiunStringField from type [$fieldType]");
		switch ($fieldType)
		{
			case 'vCountryContextField':
				$this->field = new VidiunCountryContextField();
				break;
				
			case 'vIpAddressContextField':
				$this->field = new VidiunIpAddressContextField();
				break;
				
			case 'vUserAgentContextField':
				$this->field = new VidiunUserAgentContextField();
				break;
				
			case 'vUserEmailContextField':
				$this->field = new VidiunUserEmailContextField();
				break;
				
			case 'vCoordinatesContextField':
				$this->field = new VidiunCoordinatesContextField();
				break;

			case 'vAnonymousIPContextField':
				$this->field = new VidiunAnonymousIPContextField();
				break;

			case 'vEvalStringField':
			    $this->field = new VidiunEvalStringField();
			    break;
			
			case 'vObjectIdField':
			    $this->field = new VidiunObjectIdField();
			    break;				
				
			case 'vEvalStringField':
				$this->field = new VidiunEvalStringField();
				break;
				
			case 'vObjectIdField':
				$this->field = new VidiunObjectIdField();
				break;
				
			default:
				$this->field = VidiunPluginManager::loadObject('VidiunStringField', $fieldType);
				break;
		}
		
		if($this->field)
			$this->field->fromObject($dbObject->getField());
	}
}
