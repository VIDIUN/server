<?php
/**
 * @package plugins.eventNotification
 * @subpackage api.objects
 */
class VidiunEventNotificationParameter extends VidiunObject
{
	/**
	 * The key in the subject and body to be replaced with the dynamic value
	 * @var string
	 */
	public $key;

	/**
	 * @var string
	 */
	public $description;
	
	/**
	 * The dynamic value to be placed in the final output
	 * @var VidiunStringValue
	 */
	public $value;
	
	private static $map_between_objects = array
	(
		'key',
		'description',
		'value',
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vEventNotificationParameter();
			
		return parent::toObject($dbObject, $skip);
	}
	 
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject vEventValueCondition */
		parent::doFromObject($dbObject, $responseProfile);
		
		$valueType = get_class($dbObject->getValue());
		VidiunLog::debug("Loading VidiunStringValue from type [$valueType]");
		switch ($valueType)
		{
			case 'vMetadataField':
				$this->value = new VidiunMetadataField();
				break;
				
			case 'vStringValue':
				$this->value = new VidiunStringValue();
				break;
				
			case 'vEvalStringField':
				$this->value = new VidiunEvalStringField();
				break;
				
			default:
				$this->value = VidiunPluginManager::loadObject('VidiunStringValue', $valueType);
				break;
		}
		
		if($this->value)
			$this->value->fromObject($dbObject->getValue());
	}
}