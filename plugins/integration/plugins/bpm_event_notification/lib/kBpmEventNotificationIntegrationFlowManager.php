<?php
/**
 * @package plugins.bpmEventNotificationIntegration
 * @subpackage lib.events
 */
class vBpmEventNotificationIntegrationFlowManager implements vBatchJobStatusEventConsumer
{
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::updatedJob()
	 */
	public function updatedJob(BatchJob $dbBatchJob)
	{
		$data = $dbBatchJob->getData();
		/* @var $data vIntegrationJobData */
		
		$triggerData = $data->getTriggerData();
		/* @var $triggerData vBpmEventNotificationIntegrationJobTriggerData */
		
		$template = EventNotificationTemplatePeer::retrieveByPK($triggerData->getTemplateId());
		/* @var $template BusinessProcessNotificationTemplate */
		
		if(!$template)
		{
			VidiunLog::err("Template id [" . $triggerData->getTemplateId() . "] not found");
			return true;
		}
		
		$object = $dbBatchJob->getObject();
		if($object)
		{
			$template->addCaseId($object, $triggerData->getCaseId(), $triggerData->getBusinessProcessId());
		}
		
		return true;
	}

	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{
		if(		$dbBatchJob->getJobType() == IntegrationPlugin::getBatchJobTypeCoreValue(IntegrationBatchJobType::INTEGRATION) 
			&&	$dbBatchJob->getStatus() == BatchJob::BATCHJOB_STATUS_DONT_PROCESS 
			&&	$dbBatchJob->getData()->getTriggerType() == BpmEventNotificationIntegrationPlugin::getIntegrationTriggerCoreValue(BpmEventNotificationIntegrationTrigger::BPM_EVENT_NOTIFICATION)
		)
		{
			return true;
		}
				
		return false;
	}

	
}