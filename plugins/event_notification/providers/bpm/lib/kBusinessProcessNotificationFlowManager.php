<?php
/**
 * @package plugins.businessProcessNotification
 */
class vBusinessProcessNotificationFlowManager implements vBatchJobStatusEventConsumer, vObjectDeletedEventConsumer
{
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::shouldConsumeJobStatusEvent()
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{
		if($dbBatchJob->getJobType() != EventNotificationPlugin::getBatchJobTypeCoreValue(EventNotificationBatchType::EVENT_NOTIFICATION_HANDLER))
			return false;
			
		if($dbBatchJob->getJobSubType() != BusinessProcessNotificationPlugin::getBusinessProcessNotificationTemplateTypeCoreValue(BusinessProcessNotificationTemplateType::BPM_START))
			return false;
			
		if($dbBatchJob->getStatus() != BatchJob::BATCHJOB_STATUS_FINISHED)
			return false;
			
		return true;	
	}
	
	/* (non-PHPdoc)
	 * @see vBatchJobStatusEventConsumer::updatedJob()
	 */
	public function updatedJob(BatchJob $dbBatchJob)
	{
		$this->onBusinessProcessStart($dbBatchJob, $dbBatchJob->getData());
		return true;
	}
	
	private function onBusinessProcessStart(BatchJob $dbBatchJob, vBusinessProcessNotificationDispatchJobData $data)
	{
		$object = $data->getObject();
		$template = EventNotificationTemplatePeer::retrieveByPK($data->getTemplateId());
		if($template instanceof BusinessProcessNotificationTemplate)
		{
			$caseId = $data->getCaseId();
			$template->addCaseId($object, $caseId);
		}
		
		return true;
	}

	/* (non-PHPdoc)
	 * @see vObjectDeletedEventConsumer::objectDeleted()
	 */
	public function objectDeleted(BaseObject $object, BatchJob $raisedJob = null)
	{
		$scope = new vEventNotificationScope();
		$scope->setObject($object);
		if($raisedJob)
			$scope->setParentRaisedJob($raisedJob);
		
		$templateIds = BusinessProcessNotificationTemplate::getCaseTemplatesIds($object);
		foreach($templateIds as $templateId)
		{
			$notificationTemplate = EventNotificationTemplatePeer::retrieveByPK($templateId);
			if (!$notificationTemplate)
			{
				VidiunLog::info ("Notification template with ID [$templateId] could not be found.");
				continue;
			}
			
			/* @var $notificationTemplate BusinessProcessStartNotificationTemplate */
			if($notificationTemplate->getStatus() != EventNotificationTemplateStatus::ACTIVE || !$notificationTemplate->getAbortOnDeletion())
			{
				continue;
			}
			
			if($notificationTemplate->getPartnerId())
			{
				$scope->setPartnerId($notificationTemplate->getPartnerId());
			}
				
			$notificationTemplate->abort($scope);
		}
		return true;
	}

	/* (non-PHPdoc)
	 * @see vObjectDeletedEventConsumer::shouldConsumeDeletedEvent()
	 */
	public function shouldConsumeDeletedEvent(BaseObject $object)
	{
		$templates = BusinessProcessNotificationTemplate::getCaseTemplatesIds($object);
		if (count($templates))
			return true;
			
		return false;
	}
}