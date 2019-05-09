<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunBatchJobFilter extends VidiunBatchJobBaseFilter
{
	protected function toDynamicJobSubTypeValues($jobType, $jobSubTypeIn)
	{
		$data = new VidiunJobData();
		switch($jobType)
		{
			case VidiunBatchJobType::BULKUPLOAD:
				$data = new VidiunBulkUploadJobData();
				break;
				
			case VidiunBatchJobType::CONVERT:
				$data = new VidiunConvertJobData();
				break;
				
			case VidiunBatchJobType::CONVERT_PROFILE:
				$data = new VidiunConvertProfileJobData();
				break;
				
			case VidiunBatchJobType::EXTRACT_MEDIA:
				$data = new VidiunExtractMediaJobData();
				break;
				
			case VidiunBatchJobType::IMPORT:
				$data = new VidiunImportJobData();
				break;
				
			case VidiunBatchJobType::POSTCONVERT:
				$data = new VidiunPostConvertJobData();
				break;
				
			case VidiunBatchJobType::MAIL:
				$data = new VidiunMailJobData();
				break;
				
			case VidiunBatchJobType::NOTIFICATION:
				$data = new VidiunNotificationJobData();
				break;
				
			case VidiunBatchJobType::BULKDOWNLOAD:
				$data = new VidiunBulkDownloadJobData();
				break;
				
			case VidiunBatchJobType::FLATTEN:
				$data = new VidiunFlattenJobData();
				break;
				
			case VidiunBatchJobType::PROVISION_PROVIDE:
			case VidiunBatchJobType::PROVISION_DELETE:	
				$data = new VidiunProvisionJobData();
				break;
				
			case VidiunBatchJobType::CONVERT_COLLECTION:
				$data = new VidiunConvertCollectionJobData();
				break;
				
			case VidiunBatchJobType::STORAGE_EXPORT:
				$data = new VidiunStorageExportJobData();
				break;
				
			case VidiunBatchJobType::STORAGE_DELETE:
				$data = new VidiunStorageDeleteJobData();
				break;
				
			case VidiunBatchJobType::INDEX:
				$data = new VidiunIndexJobData();
				break;
				
			case VidiunBatchJobType::COPY:
				$data = new VidiunCopyJobData();
				break;
				
			case VidiunBatchJobType::DELETE:
				$data = new VidiunDeleteJobData();
				break;

			case VidiunBatchJobType::DELETE_FILE:
				$data = new VidiunDeleteFileJobData();
				break;
				
			case VidiunBatchJobType::MOVE_CATEGORY_ENTRIES:
				$data = new VidiunMoveCategoryEntriesJobData();
				break;
				
			default:
				$data = VidiunPluginManager::loadObject('VidiunJobData', $jobType);
		}
		
		if(!$data)
		{
			VidiunLog::err("Data type not found for job type [$jobType]");
			return null;
		}
			
		$jobSubTypeArray = explode(baseObjectFilter::IN_SEPARATOR, $jobSubTypeIn);
		$dbJobSubTypeArray = array();
		foreach($jobSubTypeArray as $jobSubType)
			$dbJobSubTypeArray[] = $data->toSubType($jobSubType);
			
		$dbJobSubType = implode(baseObjectFilter::IN_SEPARATOR, $dbJobSubTypeArray);
		return $dbJobSubType;
	}

	/* (non-PHPdoc)
	 * @see VidiunFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new BatchJobFilter();
	}
	
	/**
	 * @param int $jobType
	 * @return BatchJobFilter
	 */
	public function toFilter($jobType = null)
	{
		$batchJobFilter = $this->toObject(new BatchJobFilter(false));
		
		if(!is_null($jobType) && !is_null($this->jobSubTypeIn))
		{
			$jobSubTypeIn = $this->toDynamicJobSubTypeValues($jobType, $this->jobSubTypeIn);
			$batchJobFilter->set('_in_job_sub_type', $jobSubTypeIn);
		}
	
		if(!is_null($jobType) && !is_null($this->jobSubTypeNotIn))
		{
			$jobSubTypeNotIn = $this->toDynamicJobSubTypeValues($jobType, $this->jobSubTypeNotIn);
			$batchJobFilter->set('_notin_job_sub_type', $jobSubTypeNotIn);
		}
		
		return $batchJobFilter;
	}
}
