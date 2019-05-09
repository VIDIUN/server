<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBatchJob extends VidiunObject implements IFilterable
{
	
	/**
	 * @var bigint
	 * @readonly
	 * @filter eq,gte
	 */
	public $id;
	
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in,notin
	 */
	public $partnerId;
	
	
	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;
	
	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;
	
	/**
	 * @var time
	 * @readonly
	 */
	public $deletedAt;
	
	/**
	 * @var int
	 * @readonly
	 */
	public $lockExpiration;
	
	/**
	 * @var int
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $executionAttempts;
	
	/**
	 * @var int
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $lockVersion;
	
	/**
	 * @var string
	 * @filter eq
	 */
	public $entryId;
	
	/**
	 * @var string
	 */
	public $entryName;
	
	/**
	 * @var VidiunBatchJobType
	 * @readonly 
	 * @filter eq,in,notin
	 */
    public $jobType;
    
	/**
	 * @var int
	 * @filter eq,in,notin
	 */
    public $jobSubType;
    
	/**
	 * @var VidiunJobData
	 */
    public $data;

    /**
	 * @var VidiunBatchJobStatus
	 * @filter eq,in,notin,order
	 */
    public $status;
    
    /**
	 * @var int
	 */
    public $abort;
    
    /**
	 * @var int
	 */
    public $checkAgainTimeout;

    /**
	 * @var string
	 */
    public $message ;
    
    /**
	 * @var string
	 */
    public $description ;
    
    /**
	 * @var int
	 * @filter gte,lte,eq,in,notin,order
	 */
    public $priority ;
    
    /**
     * @var VidiunBatchHistoryDataArray
     */
    public $history ;
    
    /**
     * The id of the bulk upload job that initiated this job
	 * @var int
	 */    
    public $bulkJobId;
    
    /**
     * @var int
     * @filter gte,lte,eq
     */
    public $batchVersion;
    
    
    /**
     * When one job creates another - the parent should set this parentJobId to be its own id.
	 * @var int
	 */    
    public $parentJobId;
    
    
    /**
     * The id of the root parent job
	 * @var int
	 */    
    public $rootJobId;
    
    
    /**
     * The time that the job was pulled from the queue
	 * @var int
	 * @filter gte,lte,order
	 */    
    public $queueTime;
    
    
    /**
     * The time that the job was finished or closed as failed
	 * @var int
	 * @filter gte,lte,order
	 */    
    public $finishTime;
    
    
    /**
	 * @var VidiunBatchJobErrorTypes
	 * @filter eq,in,notin
	 */    
    public $errType;
    
    
    /**
	 * @var int
	 * @filter eq,in,notin
	 */    
    public $errNumber;
    
    
    /**
	 * @var int
	 * @filter lt,gt,order
	 */    
    public $estimatedEffort;
    
    /**
     * @var int
     * @filter lte,gte
     */
    public $urgency;
    
    /**
	 * @var int
	 */    
    public $schedulerId;
	
    
    /**
	 * @var int
	 */    
    public $workerId;
	
    
    /**
	 * @var int
	 */    
    public $batchIndex;
	
    
    /**
	 * @var int
	 */    
    public $lastSchedulerId;
	
    
    /**
	 * @var int
	 */    
    public $lastWorkerId;
    
    /**
	 * @var int
	 */    
    public $dc;
    
    /**
     * @var string
     */
    public $jobObjectId;

    /**
     * @var int
     */
	public $jobObjectType;
	
	private static $map_between_objects = array
	(
		"id" ,
		"partnerId" ,
		"createdAt" , "updatedAt" , 
		"entryId" ,
		"jobType" , 
	 	"status" ,  
		"message", "description" , "parentJobId" ,
		"rootJobId", "bulkJobId" , "priority" ,
		"queueTime" , "finishTime" ,  "errType", "errNumber", 
		"dc",
		"lastSchedulerId", "lastWorkerId" , 
		"history",
		"jobObjectId" => "objectId", "jobObjectType" => "objectType"
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	    
	public function fromStatisticsObject($dbBatchJob, $dbLockObj = null)
	{
		$dbBatchJobLock = BatchJobLockPeer::retrieveByPK($dbBatchJob->getId());
		$this->fromBatchJob($dbBatchJob, $dbBatchJobLock);
		
		if(!($dbBatchJob instanceof BatchJob))
			return $this;
			
		$entry = $dbBatchJob->getEntry(true);
		if($entry)
			$this->entryName = $entry->getName();
		
		return $this;
	}
	    
	public function fromData(BatchJob $dbBatchJob, $dbData)
	{
		if(!$dbData)
			return;
				
		switch(get_class($dbData))
		{
			case 'vConvartableJobData':
				$this->data = new VidiunConvartableJobData();
				break;
				
			case 'vConvertJobData':
				$this->data = new VidiunConvertJobData();
				break;
				
			case 'vConvertProfileJobData':
				$this->data = new VidiunConvertProfileJobData();
				break;
				
			case 'vExtractMediaJobData':
				$this->data = new VidiunExtractMediaJobData();
				break;
				
			case 'vImportJobData':
				$this->data = new VidiunImportJobData();
				break;
				
			case 'vSshImportJobData':
				$this->data = new VidiunSshImportJobData();
				break;
				
			case 'vPostConvertJobData':
				$this->data = new VidiunPostConvertJobData();
				break;
				
			case 'vMailJobData':
				$this->data = new VidiunMailJobData();
				break;
				
			case 'vNotificationJobData':
				$this->data = new VidiunNotificationJobData();
				break;
				
			case 'vBulkDownloadJobData':
				$this->data = new VidiunBulkDownloadJobData();
				break;
				
			case 'vFlattenJobData':
				$this->data = new VidiunFlattenJobData();
				break;
			
			case 'vProvisionJobData':
				$this->data = new VidiunProvisionJobData();
				break;
				
			case 'vAkamaiProvisionJobData':
				$this->data = new VidiunAkamaiProvisionJobData();
				break;	

			case 'vAkamaiUniversalProvisionJobData':
				$this->data = new VidiunAkamaiUniversalProvisionJobData();
				break;
				
			case 'vConvertCollectionJobData':
				$this->data = new VidiunConvertCollectionJobData();
				break;
				
			case 'vStorageExportJobData':
				$this->data = new VidiunStorageExportJobData();
				break;
				
			case 'vAmazonS3StorageExportJobData':
				$this->data = new VidiunAmazonS3StorageExportJobData();
				break;
				
			case 'vMoveCategoryEntriesJobData':
				$this->data = new VidiunMoveCategoryEntriesJobData();
				break;
				
			case 'vStorageDeleteJobData':
				$this->data = new VidiunStorageDeleteJobData();
				break;
				
			case 'vCaptureThumbJobData':
				$this->data = new VidiunCaptureThumbJobData();
				break;
				
			case 'vMoveCategoryEntriesJobData':
			    $this->data = new VidiunMoveCategoryEntriesJobData();
			    break;

			case 'kIndexJobData':
				$this->data = new VidiunIndexJobData();
				break;
				
			case 'vCopyJobData':
				$this->data = new VidiunCopyJobData();
				break;
				
			case 'vDeleteJobData':
				$this->data = new VidiunDeleteJobData();
				break;

			case 'vDeleteFileJobData':
				$this->data = new VidiunDeleteFileJobData();
				break;
				
			case 'vConvertLiveSegmentJobData':
				$this->data = new VidiunConvertLiveSegmentJobData();
				break;
				
			case 'vConcatJobData':
				$this->data = new VidiunConcatJobData();
				break;
				
			case 'vCopyPartnerJobData':
				$this->data = new VidiunCopyPartnerJobData();
				break;
				
			case 'vSyncCategoryPrivacyContextJobData':
				$this->data = new VidiunSyncCategoryPrivacyContextJobData();
				break;
			
			case 'vLiveReportExportJobData':
				$this->data = new VidiunLiveReportExportJobData();
				break;
			
			case 'vRecalculateResponseProfileCacheJobData':
				$this->data = new VidiunRecalculateResponseProfileCacheJobData();
				break;

			case 'vLiveToVodJobData':
				$this->data = new VidiunLiveToVodJobData();
				break;

			case 'vCopyCaptionsJobData':
				$this->data = new VidiunCopyCaptionsJobData();
				break;

			case 'vUsersCsvJobData':
				$this->data = new VidiunUsersCsvJobData();
				break;
			
			case 'vClipConcatJobData':
				$this->data = new VidiunClipConcatJobData();
				break;

			case 'vCopyCuePointsJobData':
				$this->data = new VidiunCopyCuePointsJobData();
				break;

			case 'vMultiClipCopyCuePointsJobData':
				$this->data = new VidiunMultiClipCopyCuePointsJobData();
				break;

			case 'vReportExportJobData':
				$this->data = new VidiunReportExportJobData();
				break;

			default:
				if($dbData instanceof vBulkUploadJobData)
				{
					$this->data = VidiunPluginManager::loadObject('VidiunBulkUploadJobData', $dbBatchJob->getJobSubType());
					if(is_null($this->data))
						VidiunLog::err("Unable to init VidiunBulkUploadJobData for sub-type [" . $dbBatchJob->getJobSubType() . "]");
				}
				else if($dbData instanceof vImportJobData)
				{
					$this->data = VidiunPluginManager::loadObject('VidiunImportJobData', get_class($dbData));
					if(is_null($this->data))
						VidiunLog::err("Unable to init VidiunImportJobData for class [" . get_class($dbData) . "]");
				}
				else
				{
					$this->data = VidiunPluginManager::loadObject('VidiunJobData', $this->jobType, array('coreJobSubType' => $dbBatchJob->getJobSubType()));
				}
		}
		
		if(is_null($this->data))
			VidiunLog::err("Unable to init VidiunJobData for job type [{$this->jobType}] sub-type [" . $dbBatchJob->getJobSubType() . "]");
			
		if($this->data)
			$this->data->fromObject($dbData);
	}
	
	public function fromLockObject(BatchJob $dbBatchJob, BatchJobLock $dbBatchJobLock) 
	{
		$this->lockExpiration = $dbBatchJobLock->getExpiration();
		$this->executionAttempts = $dbBatchJobLock->getExecutionAttempts();
		$this->lockVersion = $dbBatchJobLock->getVersion();
		$this->checkAgainTimeout = $dbBatchJobLock->getStartAt(null);
		$this->estimatedEffort = $dbBatchJobLock->getEstimatedEffort();
		
		$this->schedulerId = $dbBatchJobLock->getSchedulerId();
		$this->workerId = $dbBatchJobLock->getWorkerId();
	}
	
	public function fromBatchJob($dbBatchJob, BatchJobLock $dbBatchJobLock = null) 
	{
		parent::fromObject($dbBatchJob);
		
		$this->queueTime = $dbBatchJob->getQueueTime(null); // to return the timestamp and not string
		$this->finishTime = $dbBatchJob->getFinishTime(null); // to return the timestamp and not string
		
		if(!($dbBatchJob instanceof BatchJob))
			return $this;
			
		$dbData = $dbBatchJob->getData();
		$this->fromData($dbBatchJob, $dbData);
		if($this->data)
			$this->jobSubType = $this->data->fromSubType($dbBatchJob->getJobSubType());
		
		if($dbBatchJobLock) {
			$this->fromLockObject($dbBatchJob, $dbBatchJobLock);
		} else {
			$this->lockVersion = $dbBatchJob->getLockInfo()->getLockVersion();
			$this->estimatedEffort = $dbBatchJob->getLockInfo()->getEstimatedEffort();
		}
		
		return $this;
	}
	
	public function toData(BatchJob $dbBatchJob)
	{
		$dbData = null;
		
		if(is_null($this->jobType))
			$this->jobType = vPluginableEnumsManager::coreToApi('BatchJobType', $dbBatchJob->getJobType());
		
		switch($dbBatchJob->getJobType())
		{
			case VidiunBatchJobType::BULKUPLOAD:
				$dbData = new vBulkUploadJobData();
				if(is_null($this->data))
					$this->data = new VidiunBulkUploadJobData();
				break;
				
			case VidiunBatchJobType::CONVERT:
				$dbData = new vConvertJobData();
				if(is_null($this->data))
					$this->data = new VidiunConvertJobData();
				break;
				
			case VidiunBatchJobType::CONVERT_PROFILE:
				$dbData = new vConvertProfileJobData();
				if(is_null($this->data))
					$this->data = new VidiunConvertProfileJobData();
				break;
				
			case VidiunBatchJobType::EXTRACT_MEDIA:
				$dbData = new vExtractMediaJobData();
				if(is_null($this->data))
					$this->data = new VidiunExtractMediaJobData();
				break;
				
			case VidiunBatchJobType::IMPORT:
				$dbData = new vImportJobData();
				if(is_null($this->data))
					$this->data = new VidiunImportJobData();
				break;
				
			case VidiunBatchJobType::POSTCONVERT:
				$dbData = new vPostConvertJobData();
				if(is_null($this->data))
					$this->data = new VidiunPostConvertJobData();
				break;
				
			case VidiunBatchJobType::MAIL:
				$dbData = new vMailJobData();
				if(is_null($this->data))
					$this->data = new VidiunMailJobData();
				break;
				
			case VidiunBatchJobType::NOTIFICATION:
				$dbData = new vNotificationJobData();
				if(is_null($this->data))
					$this->data = new VidiunNotificationJobData();
				break;
				
			case VidiunBatchJobType::BULKDOWNLOAD:
				$dbData = new vBulkDownloadJobData();
				if(is_null($this->data))
					$this->data = new VidiunBulkDownloadJobData();
				break;
				
			case VidiunBatchJobType::FLATTEN:
				$dbData = new vFlattenJobData();
				if(is_null($this->data))
					$this->data = new VidiunFlattenJobData();
				break;
				
			case VidiunBatchJobType::PROVISION_PROVIDE:
			case VidiunBatchJobType::PROVISION_DELETE:
				$jobSubType = $dbBatchJob->getJobSubType();
				$dbData = vAkamaiProvisionJobData::getInstance($jobSubType);
				if(is_null($this->data))
					$this->data = VidiunProvisionJobData::getJobDataInstance($jobSubType);

				break;
				
			case VidiunBatchJobType::CONVERT_COLLECTION:
				$dbData = new vConvertCollectionJobData();
				if(is_null($this->data))
					$this->data = new VidiunConvertCollectionJobData();
				break;
				
			case VidiunBatchJobType::STORAGE_EXPORT:
				$dbData = new vStorageExportJobData();
				if(is_null($this->data))
					$this->data = new VidiunStorageExportJobData();
				break;
				
			case VidiunBatchJobType::MOVE_CATEGORY_ENTRIES:
				$dbData = new vMoveCategoryEntriesJobData();
				if(is_null($this->data))
					$this->data = new VidiunMoveCategoryEntriesJobData();
				break;
				
			case VidiunBatchJobType::STORAGE_DELETE:
				$dbData = new vStorageDeleteJobData();
				if(is_null($this->data))
					$this->data = new VidiunStorageDeleteJobData();
				break;
				
			case VidiunBatchJobType::CAPTURE_THUMB:
				$dbData = new vCaptureThumbJobData();
				if(is_null($this->data))
					$this->data = new VidiunCaptureThumbJobData();
				break;
				
			case VidiunBatchJobType::INDEX:
				$dbData = new kIndexJobData();
				if(is_null($this->data))
					$this->data = new VidiunIndexJobData();
				break;
				
			case VidiunBatchJobType::COPY:
				$dbData = new vCopyJobData();
				if(is_null($this->data))
					$this->data = new VidiunCopyJobData();
				break;
				
			case VidiunBatchJobType::DELETE:
				$dbData = new vDeleteJobData();
				if(is_null($this->data))
					$this->data = new VidiunDeleteJobData();
				break;

			case VidiunBatchJobType::DELETE_FILE:
				$dbData = new vDeleteFileJobData();
				if(is_null($this->data))
					$this->data = new VidiunDeleteFileJobData();
				break;
				
			case VidiunBatchJobType::CONVERT_LIVE_SEGMENT:
				$dbData = new vConvertLiveSegmentJobData();
				if(is_null($this->data))
					$this->data = new VidiunConvertLiveSegmentJobData();
				break;
				
			case VidiunBatchJobType::CONCAT:
				$dbData = new vConcatJobData();
				if(is_null($this->data))
					$this->data = new VidiunConcatJobData();
				break;
					
			case VidiunBatchJobType::COPY_PARTNER:
				$dbData = new vCopyPartnerJobData();
				if(is_null($this->data))
					$this->data = new VidiunCopyPartnerJobData();
				break;
					
			case VidiunBatchJobType::RECALCULATE_CACHE:
				switch($dbBatchJob->getJobSubType())
				{
					case RecalculateCacheType::RESPONSE_PROFILE:
						$dbData = new vRecalculateResponseProfileCacheJobData();
						if(is_null($this->data))
							$this->data = new VidiunRecalculateResponseProfileCacheJobData();
						break;
				}
				break;
			
			case VidiunBatchJobType::LIVE_TO_VOD:
				$dbData = new vLiveToVodJobData();
				if(is_null($this->data))
					$this->data = new VidiunLiveToVodJobData();
 				break;


			case VidiunBatchJobType::CLIP_CONCAT:
				$dbData = new vClipConcatJobData();
				if(is_null($this->data))
					$this->data = new VidiunClipConcatJobData();
				break;

			case VidiunBatchJobType::COPY_CUE_POINTS:
				switch ($dbBatchJob->getJobSubType()) {
					case CopyCuePointJobType::MULTI_CLIP:
						$dbData = new vMultiClipCopyCuePointsJobData();
						if(is_null($this->data))
							$this->data = new VidiunMultiClipCopyCuePointsJobData();
						break;
					case CopyCuePointJobType::LIVE_CLIPPING:
						$dbData = new vLiveToVodJobData();
						if(is_null($this->data))
							$this->data = new VidiunLiveToVodJobData();
						break;
					default:
						$dbData = new vCopyCuePointsJobData();
						if(is_null($this->data))
							$this->data = new VidiunCopyCuePointsJobData();
						break;
				}
				break;

			case VidiunBatchJobType::COPY_CAPTIONS:
				$dbData = new vCopyCaptionsJobData();
				if(is_null($this->data))
					$this->data = new VidiunCopyCaptionsJobData();
				break;

			case VidiunBatchJobType::USERS_CSV:
				$dbData = new vUsersCsvJobData();
				if(is_null($this->data))
					$this->data = new VidiunUsersCsvJobData();
				break;

			default:
				$dbData = VidiunPluginManager::loadObject('vJobData', $dbBatchJob->getJobType());
				if(is_null($this->data)) {
					$this->data = VidiunPluginManager::loadObject('VidiunJobData', $this->jobType);
				}
		}
		
		if(is_null($dbBatchJob->getData()))
			$dbBatchJob->setData($dbData);
	
		if($this->data instanceof VidiunJobData)
		{
			$dbData = $this->data->toObject($dbBatchJob->getData());
			$dbBatchJob->setData($dbData);
		}
		
		return $dbData;
	}
	
	public function toObject($dbBatchJob = null, $props_to_skip = array())
	{
		if(is_null($dbBatchJob))
			$dbBatchJob = new BatchJob();

		$dbBatchJob = parent::toObject($dbBatchJob);
		if($this->abort)
			$dbBatchJob->setExecutionStatus(BatchJobExecutionStatus::ABORTED);
		
		if (!is_null($this->data))
		    $this->toData($dbBatchJob);
		if(!is_null($this->jobSubType) && $this->data instanceof VidiunJobData)
			$dbBatchJob->setJobSubType($this->data->toSubType($this->jobSubType));
		
		return $dbBatchJob;
	}   
	
	public function getExtraFilters()
	{
		return array();
	}
	
	public function getFilterDocs()
	{
		return array();
	} 
}
