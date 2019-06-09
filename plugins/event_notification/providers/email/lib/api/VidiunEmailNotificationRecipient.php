<?php
/**
 * @package plugins.emailNotification
 * @subpackage api.objects
 */
class VidiunEmailNotificationRecipient extends VidiunObject
{
	/**
	 * Recipient e-mail address
	 * @var VidiunStringValue
	 */
	public $email;
	
	/**
	 * Recipient name
	 * @var VidiunStringValue
	 */
	public $name;
	
	private static $map_between_objects = array
	(
		'email',
		'name',
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
			$dbObject = new vEmailNotificationRecipient();
			
		return parent::toObject($dbObject, $skip);
	}
	 
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $dbObject vEmailNotificationRecipient */
		parent::doFromObject($dbObject, $responseProfile);
		
		
		$emailType = get_class($dbObject->getEmail());
		switch ($emailType)
		{
			case 'vStringValue':
				$this->email = new VidiunStringValue();
				break;
				
			case 'vEvalStringField':
				$this->email = new VidiunEvalStringField();
				break;
				
			case 'vUserEmailContextField':
				$this->email = new VidiunUserEmailContextField();
				break;
				
			default:
				$this->email = VidiunPluginManager::loadObject('VidiunStringValue', $emailType);
				break;
		}
		if($this->email)
			$this->email->fromObject($dbObject->getEmail());
		
			
		$nameType = get_class($dbObject->getName());
		switch ($nameType)
		{
			case 'vStringValue':
				$this->name = new VidiunStringValue();
				break;
				
			case 'vEvalStringField':
				$this->name = new VidiunEvalStringField();
				break;
				
			default:
				$this->name = VidiunPluginManager::loadObject('VidiunStringValue', $nameType);
				break;
		}
		if($this->name)
			$this->name->fromObject($dbObject->getName());
	}
}
