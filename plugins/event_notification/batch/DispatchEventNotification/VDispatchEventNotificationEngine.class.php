<?php
/**
 * @package plugins.eventNotification
 * @subpackage Scheduler
 */
abstract class VDispatchEventNotificationEngine
{	
	
	/**
	 * @param VidiunEventNotificationTemplate $eventNotificationTemplate
	 * @param VidiunEventNotificationDispatchJobData $data
	 */
	abstract public function dispatch(VidiunEventNotificationTemplate $eventNotificationTemplate, VidiunEventNotificationDispatchJobData &$data);
}
