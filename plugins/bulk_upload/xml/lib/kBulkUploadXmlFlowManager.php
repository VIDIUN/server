<?php

class vBulkUploadXmlFlowManager implements vBatchJobStatusEventConsumer
{
	/**
	 * @param BatchJob $dbBatchJob
	 * @return bool true if should continue to the next consumer
	 */
	public function updatedJob(BatchJob $dbBatchJob)
	{
		VidiunLog::debug("Handling finished ExtractMedia job!");
		
		$profile = myPartnerUtils::getConversionProfile2ForEntry($dbBatchJob->getEntryId());
		if(is_null($profile))
		{
			VidiunLog::err("no profile found for entry " . $dbBatchJob->getEntryId());
			return true;
		}
		$mediaInfoXslt = $profile->getMediaInfoXslTransformation();
		if (!$mediaInfoXslt)
		{
			return true;
		}
		
		$mediaInfo = mediaInfoPeer::retrieveByPk($dbBatchJob->getData()->getMediaInfoId());
		$mediaInfoRawData = $mediaInfo->getRawDataXml();
		
		$transformedXml = vXml::transformXmlUsingXslt($mediaInfoRawData, $mediaInfoXslt, array("entryId" => $dbBatchJob->getEntryId()));
		$xml = new VDOMDocument();
		if(!$xml->loadXML($transformedXml))
		{
			VidiunLog::err("Could not load xml string");
			return true;
		}
		
		if(!$xml->getElementsByTagName("entryId")->item(0))
		{
			VidiunLog::err("XML structure is incorrect - must contain tag entry ID");
			return true;
		}
		$transformedXml = $xml->saveXML();
		
		//Save the file to a shared temporary location
		$tmpSharedFolder = vConf::get("shared_temp_folder") . DIRECTORY_SEPARATOR . "bulkupload";
		$fileName = $dbBatchJob->getEntryId() . '_update_' . uniqid() . ".xml";
		$filePath = $tmpSharedFolder . DIRECTORY_SEPARATOR. $fileName;
		vFile::fullMkdir($filePath,0755);
		$res = file_put_contents($filePath, $transformedXml);
		chmod($filePath, 0640);
		
		$jobData = new vBulkUploadXmlJobData();
		$jobData->setFileName($fileName);
		$jobData->setFilePath($filePath);
		$jobData->setBulkUploadObjectType(BulkUploadObjectType::ENTRY);
		$jobData->setObjectData(new vBulkUploadEntryData());
		$bulkUploadCoreType = BulkUploadXmlPlugin::getBulkUploadTypeCoreValue(BulkUploadXmlType::XML);
		
		vJobsManager::addBulkUploadJob($dbBatchJob->getPartner(), $jobData, $bulkUploadCoreType);
		
		return true;
	}
	
	/**
	 * @param BatchJob $dbBatchJob
	 * @return bool true if the consumer should handle the event
	 */
	public function shouldConsumeJobStatusEvent(BatchJob $dbBatchJob)
	{
		if ($dbBatchJob->getStatus() == BatchJob::BATCHJOB_STATUS_FINISHED && $dbBatchJob->getJobType() == BatchJobType::EXTRACT_MEDIA)
		{
			return true;
		}
		
		return false;
	}
	
}
