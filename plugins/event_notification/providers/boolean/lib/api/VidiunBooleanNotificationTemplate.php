<?php
/**
 * @package plugins.booleanNotification
 * @subpackage api.objects
 */
class VidiunBooleanNotificationTemplate extends VidiunEventNotificationTemplate
{
	public function __construct()
	{
		$this->type = BooleanNotificationPlugin::getApiValue(BooleanNotificationTemplateType::BOOLEAN);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if(is_null($dbObject))
			$dbObject = new BooleanNotificationTemplate();
		return parent::toObject($dbObject, $propertiesToSkip);
	}

	/* (non-PHPdoc)
 	* @see VidiunObject::validateForUpdate()
 	*/
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		$propertiesToSkip[] = 'type';
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
}