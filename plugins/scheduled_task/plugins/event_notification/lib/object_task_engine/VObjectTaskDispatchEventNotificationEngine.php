<?php

/**
 * @package plugins.scheduledTaskEventNotification
 * @subpackage lib.objectTaskEngine
 */
class VObjectTaskDispatchEventNotificationEngine extends VObjectTaskEntryEngineBase
{
	/**
	 * @param VidiunBaseEntry $object
	 */
	function processObject($object)
	{
		/** @var VidiunDispatchEventNotificationObjectTask $objectTask */
		$objectTask = $this->getObjectTask();
		if (is_null($objectTask))
			return;

		$client = $this->getClient();
		$templateId = $objectTask->eventNotificationTemplateId;
		$eventNotificationPlugin = VidiunEventNotificationClientPlugin::get($client);
		$scope = new VidiunEventNotificationScope();
		$scope->objectId =$object->id;
		$scope->scopeObjectType = VidiunEventNotificationEventObjectType::ENTRY;
		$eventNotificationPlugin->eventNotificationTemplate->dispatch($templateId, $scope);
	}
}