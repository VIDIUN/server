<?php
/**
 * @package plugins.eventNotification
 * @subpackage Scheduler
 */
class VAsyncDispatchEventNotification extends VJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::EVENT_NOTIFICATION_HANDLER;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $this->dispatch($job, $job->data);
	}
	
	protected function dispatch(VidiunBatchJob $job, VidiunEventNotificationDispatchJobData $data)
	{
		$this->updateJob($job, "Dispatch template [$data->templateId]", VidiunBatchJobStatus::QUEUED);
		
		$eventNotificationPlugin = VidiunEventNotificationClientPlugin::get(self::$vClient);
		$eventNotificationTemplate = $eventNotificationPlugin->eventNotificationTemplate->get($data->templateId);
		
		$engine = $this->getEngine($job->jobSubType);
		if(!$engine)
			return $this->closeJob($job, VidiunBatchJobErrorTypes::APP, VidiunBatchJobAppErrors::ENGINE_NOT_FOUND, "Engine not found", VidiunBatchJobStatus::FAILED);
		
		$this->impersonate($job->partnerId);
		$engine->dispatch($eventNotificationTemplate, $data);
		$this->unimpersonate();
		
		return $this->closeJob($job, null, null, "Dispatched", VidiunBatchJobStatus::FINISHED, $data);
	}

	/**
	 * @param VidiunEventNotificationTemplateType $type
	 * @return VDispatchEventNotificationEngine
	 */
	protected function getEngine($type)
	{
		return VidiunPluginManager::loadObject('VDispatchEventNotificationEngine', $type);
	}
}
