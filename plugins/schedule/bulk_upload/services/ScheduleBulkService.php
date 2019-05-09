<?php
/**
 * Bulk upload service is used to upload & manage events in bulk
 *
 * @service scheduleBulk
 * @package plugins.scheduleBulkUpload
 * @subpackage services
 */
class ScheduleBulkService extends VidiunBaseService
{
	/**
	 * Add new bulk upload batch job
	 * 
	 * @action addScheduleEvents
	 * @actionAlias schedule_scheduleEvent.addFromBulkUpload
	 * @param file $fileData
	 * @param VidiunBulkUploadICalJobData $bulkUploadData
	 * @return VidiunBulkUpload
	 */
	function addScheduleEventsAction($fileData, VidiunBulkUploadICalJobData $bulkUploadData = null)
	{
		$bulkUploadCoreType = BulkUploadSchedulePlugin::getBulkUploadTypeCoreValue(BulkUploadScheduleType::ICAL);
		$bulkUploadObjectCoreType = BulkUploadSchedulePlugin::getBulkUploadObjectTypeCoreValue(BulkUploadObjectScheduleType::SCHEDULE_EVENT);

		if (!$bulkUploadData)
		{
			throw new VidiunAPIException(VidiunErrors::MISSING_MANDATORY_PARAMETER, 'bulkUploadData');
		}

		if (!$bulkUploadData->eventsType)
		{
			throw new VidiunAPIException(VidiunErrors::MISSING_MANDATORY_PARAMETER, 'eventsType');
		}

		if(!$bulkUploadData->fileName)
			$bulkUploadData->fileName = $fileData["name"];
		
		$dbBulkUploadJobData = $bulkUploadData->toInsertableObject();
		/* @var $dbBulkUploadJobData vBulkUploadJobData */
		
		$dbBulkUploadJobData->setBulkUploadObjectType($bulkUploadObjectCoreType);
		$dbBulkUploadJobData->setUserId($this->getVuser()->getPuserId());
		$dbBulkUploadJobData->setFilePath($fileData["tmp_name"]);
		
		$dbJob = vJobsManager::addBulkUploadJob($this->getPartner(), $dbBulkUploadJobData, $bulkUploadCoreType);
		$dbJobLog = BatchJobLogPeer::retrieveByBatchJobId($dbJob->getId());
		if(!$dbJobLog)
			return null;
			
		$bulkUpload = new VidiunBulkUpload();
		$bulkUpload->fromObject($dbJobLog, $this->getResponseProfile());
		
		return $bulkUpload;
	}
	
	/**
	 * Add new bulk upload batch job
	 * 
	 * @action addScheduleResources
	 * @actionAlias schedule_scheduleResource.addFromBulkUpload
	 * @param file $fileData
	 * @param VidiunBulkUploadCsvJobData $bulkUploadData
	 * @return VidiunBulkUpload
	 */
	function addScheduleResourcesAction($fileData, VidiunBulkUploadCsvJobData $bulkUploadData = null)
	{	    
	    if (!$bulkUploadData)
	    {
	       $bulkUploadData = VidiunPluginManager::loadObject('VidiunBulkUploadJobData', null);
	    }
	    
		$bulkUploadObjectCoreType = BulkUploadSchedulePlugin::getBulkUploadObjectTypeCoreValue(BulkUploadObjectScheduleType::SCHEDULE_RESOURCE);
		
		if(!$bulkUploadData->fileName)
			$bulkUploadData->fileName = $fileData["name"];
		
		$dbBulkUploadJobData = $bulkUploadData->toInsertableObject();
		/* @var $dbBulkUploadJobData vBulkUploadJobData */

		$bulkUploadCoreType = vPluginableEnumsManager::apiToCore("BulkUploadType", $bulkUploadData->type);
		
		$dbBulkUploadJobData->setBulkUploadObjectType($bulkUploadObjectCoreType);
		$dbBulkUploadJobData->setUserId($this->getVuser()->getPuserId());
		$dbBulkUploadJobData->setFilePath($fileData["tmp_name"]);
		
		$dbJob = vJobsManager::addBulkUploadJob($this->getPartner(), $dbBulkUploadJobData, $bulkUploadCoreType);
		$dbJobLog = BatchJobLogPeer::retrieveByBatchJobId($dbJob->getId());
		if(!$dbJobLog)
			return null;
			
		$bulkUpload = new VidiunBulkUpload();
		$bulkUpload->fromObject($dbJobLog, $this->getResponseProfile());
		
		return $bulkUpload;
	}
}