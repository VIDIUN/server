<?php
/**
 * @package plugins.businessProcessNotification
 * @subpackage Scheduler
 */
class VDispatchBusinessProcessNotificationEngine extends VDispatchEventNotificationEngine
{
	/**
	 * @param VidiunBusinessProcessServer $server
	 * @return vBusinessProcessProvider
	 */
	public function getBusinessProcessProvider(VidiunBusinessProcessServer $server)
	{
		$provider = vBusinessProcessProvider::get($server);
		$provider->enableDebug(true);
		
		return $provider;
	}
	
	/* (non-PHPdoc)
	 * @see VDispatchEventNotificationEngine::dispatch()
	 */
	public function dispatch(VidiunEventNotificationTemplate $eventNotificationTemplate, VidiunEventNotificationDispatchJobData &$data)
	{
		$job = VJobHandlerWorker::getCurrentJob();
	
		$variables = array();
		if(is_array($data->contentParameters) && count($data->contentParameters))
		{
			foreach($data->contentParameters as $contentParameter)
			{
				/* @var $contentParameter VidiunKeyValue */
				$variables[$contentParameter->key] = $contentParameter->value;
			}		
		}
		
		switch ($job->jobSubType)
		{
			case VidiunEventNotificationTemplateType::BPM_START:
				return $this->startBusinessProcess($eventNotificationTemplate, $data, $variables);
				
			case VidiunEventNotificationTemplateType::BPM_SIGNAL:
				return $this->signalCase($eventNotificationTemplate, $data, $variables);
				
			case VidiunEventNotificationTemplateType::BPM_ABORT:
				return $this->abortCase($eventNotificationTemplate, $data);
		}
	}

	/**
	 * @param VidiunBusinessProcessStartNotificationTemplate $template
	 * @param VidiunBusinessProcessNotificationDispatchJobData $data
	 */
	public function startBusinessProcess(VidiunBusinessProcessStartNotificationTemplate $template, VidiunBusinessProcessNotificationDispatchJobData &$data, $variables)
	{	
		$provider = $this->getBusinessProcessProvider($data->server);
		VidiunLog::info("Starting business-process [{$template->processId}] with variables [" . print_r($variables, true) . "]");
		$data->caseId = $provider->startBusinessProcess($template->processId, $variables);
		VidiunLog::info("Started business-process case [{$data->caseId}]");
	}

	/**
	 * @param VidiunBusinessProcessSignalNotificationTemplate $template
	 * @param VidiunBusinessProcessNotificationDispatchJobData $data
	 */
	public function signalCase(VidiunBusinessProcessSignalNotificationTemplate $template, VidiunBusinessProcessNotificationDispatchJobData &$data, $variables)
	{
		$provider = $this->getBusinessProcessProvider($data->server);
		VidiunLog::info("Signaling business-process [{$template->processId}] case [{$data->caseId}] with message [{$template->message}] on blocking event [{$template->eventId}]");
		$provider->signalCase($data->caseId, $template->eventId, $template->message, $variables);
	}

	/**
	 * @param VidiunBusinessProcessStartNotificationTemplate $template
	 * @param VidiunBusinessProcessNotificationDispatchJobData $data
	 */
	public function abortCase(VidiunBusinessProcessAbortNotificationTemplate $template, VidiunBusinessProcessNotificationDispatchJobData &$data)
	{
		$provider = $this->getBusinessProcessProvider($data->server);
		VidiunLog::info("Aborting business-process [{$template->processId}] case [{$data->caseId}]");
		$provider->abortCase($data->caseId);
	}
}
