<?php

/**
 * @package plugins.scheduledTaskEventNotification
 * @subpackage api.objects.objectTasks
 */
class VidiunDispatchEventNotificationObjectTask extends VidiunObjectTask
{
	/**
	 * The event notification template id to dispatch
	 *
	 * @var int
	 */
	public $eventNotificationTemplateId;

	public function __construct()
	{
		$this->type = ScheduledTaskEventNotificationPlugin::getApiValue(DispatchEventNotificationObjectTaskType::DISPATCH_EVENT_NOTIFICATION);
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUsage()
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);

		$this->validatePropertyNotNull('eventNotificationTemplateId');

		myPartnerUtils::addPartnerToCriteria('EventNotificationTemplate', vCurrentContext::getCurrentPartnerId(), true);
		$eventNotificationTemplate = EventNotificationTemplatePeer::retrieveByPK($this->eventNotificationTemplateId);
		if (is_null($eventNotificationTemplate))
			throw new VidiunAPIException(VidiunEventNotificationErrors::EVENT_NOTIFICATION_TEMPLATE_NOT_FOUND, $this->eventNotificationTemplateId);
	}

	public function toObject($dbObject = null, $skip = array())
	{
		/** @var vObjectTask $dbObject */
		$dbObject = parent::toObject($dbObject, $skip);
		$dbObject->setDataValue('eventNotificationTemplateId', $this->eventNotificationTemplateId);
		return $dbObject;
	}

	public function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($srcObj, $responseProfile);

		/** @var vObjectTask $srcObj */
		$this->eventNotificationTemplateId = $srcObj->getDataValue('eventNotificationTemplateId');
	}
}