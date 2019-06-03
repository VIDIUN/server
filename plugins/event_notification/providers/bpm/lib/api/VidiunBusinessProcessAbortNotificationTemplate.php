<?php
/**
 * @package plugins.businessProcessNotification
 * @subpackage api.objects
 */
class VidiunBusinessProcessAbortNotificationTemplate extends VidiunBusinessProcessNotificationTemplate
{	
	public function __construct()
	{
		$this->type = BusinessProcessNotificationPlugin::getApiValue(BusinessProcessNotificationTemplateType::BPM_ABORT);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $propertiesToSkip = array())
	{
		if(is_null($dbObject))
			$dbObject = new BusinessProcessAbortNotificationTemplate();
			
		return parent::toObject($dbObject, $propertiesToSkip);
	}
}