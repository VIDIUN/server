<?php
/**
 * @package plugins.eventNotification
 * @subpackage api.objects
 */
class VidiunEventFieldCondition extends VidiunCondition
{	
	/**
	 * The field to be evaluated at runtime
	 * @var VidiunBooleanField
	 */
	public $field;

	private static $map_between_objects = array
	(
		'field' ,
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/**
	 * Init object type
	 */
	public function __construct()
	{
		$this->type = EventNotificationPlugin::getConditionTypeCoreValue(EventNotificationConditionType::EVENT_NOTIFICATION_FIELD);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vEventFieldCondition();
	
		return parent::toObject($dbObject, $skip);
	}
	 
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject vEventFieldCondition */
		parent::doFromObject($dbObject, $responseProfile);
		
		$fieldType = get_class($dbObject->getField());
		VidiunLog::debug("Loading VidiunBooleanField from type [$fieldType]");
		switch ($fieldType)
		{
			case 'vEvalBooleanField':
				$this->field = new VidiunEvalBooleanField();
				break;
				
			default:
				$this->field = VidiunPluginManager::loadObject('VidiunBooleanField', $fieldType);
				break;
		}
		
		if($this->field)
			$this->field->fromObject($dbObject->getField());
	}
}
