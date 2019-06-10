<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunBulkUploadJobData extends VidiunJobData
{
	/**
	 * @var string
	 * @readonly
	 */
	public $userId;
	
	/**
	 * The screen name of the user
	 * @readonly
	 * @var string
	 */
	public $uploadedBy;
	
	/**
	 * Selected profile id for all bulk entries
	 * @deprecated set this parameter on the VidiunBulkUploadEntryData instead
	 * @readonly
	 * @var int
	 */
	public $conversionProfileId;
	
	/**
	 * Created by the API
	 * @readonly
	 * @var string
	 */
	public $resultsFileLocalPath;
	
	/**
	 * Created by the API
	 * @readonly
	 * @var string
	 */
	public $resultsFileUrl;
	
	/**
	 * Number of created entries
	 * @deprecated use numOfObjects instead
	 * @readonly
	 * @var int
	 */
	public $numOfEntries;
	
	/**
	 * 
	 * Number of created objects
	 * @var int
	 * @readonly
	 */
	public $numOfObjects;
   
	/**
	 * 
	 * The bulk upload file path
	 * @var string
	 * @readonly
	 */
	public $filePath;
	
	/**
	 * Type of object for bulk upload
	 * @var VidiunBulkUploadObjectType
	 * @readonly
	 */
	public $bulkUploadObjectType;
	
	/**
	 * Friendly name of the file, used to be recognized later in the logs.
	 * @var string
	 */
	public $fileName;
	
	/**
	 * Data pertaining to the objects being uploaded
	 * @readonly
	 * @var VidiunBulkUploadObjectData
	 */
	public $objectData;
	
	/**
	 * Type of bulk upload
	 * @var VidiunBulkUploadType
	 * @readonly
	 */
	public $type;
	
	/**
	 * Recipients of the email for bulk upload success/failure
	 * @var string
	 */
	public $emailRecipients;
	
	/**
	 * Number of objects that finished on error status
	 * @var int
	 */
	public $numOfErrorObjects;

	/**
	 * privileges for the job
	 * @var string
	 */
	public $privileges;

	private static $map_between_objects = array
	(
		"userId",
		"uploadedBy",
		"conversionProfileId",
		"resultsFileLocalPath",
		"resultsFileUrl",
		"numOfEntries",
		"numOfObjects",
		"filePath",
		"fileName",
		"bulkUploadObjectType",
		"objectData",
		"numOfErrorObjects",
		"emailRecipients",
		"privileges"
	);

	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if (is_null($object_to_fill))
		{
			throw new VidiunAPIException(VidiunErrors::OBJECT_TYPE_ABSTRACT, "VidiunBulkUploadJobData");
		}
		
		return parent::toObject($object_to_fill, $props_to_skip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($source_object, $responseProfile);
		
	    /* @var $source_object vBulkUploadJobData */
	    
	    if($this->shouldGet('objectData', $responseProfile))
	    {
		    $this->objectData = null;
		    switch (get_class($source_object->getObjectData()))
		    {
		        case 'vBulkUploadEntryData':
		            $this->objectData = new VidiunBulkUploadEntryData();
		            break;
		        case 'vBulkUploadCategoryData':
		            $this->objectData = new VidiunBulkUploadCategoryData();
		            break;
		        case 'vBulkUploadCategoryUserData':
		            $this->objectData = new VidiunBulkUploadCategoryUserData();
		            break;
		        case 'vBulkUploadUserData':
		            $this->objectData = new VidiunBulkUploadUserData();
		            break;
		        case 'vBulkUploadCategoryEntryData':
		            $this->objectData = new VidiunBulkUploadCategoryEntryData();
		            break;
		        default:
		            break;
		    }
		    
		    if ($this->objectData)
		    {
		        $this->objectData->fromObject($source_object->getObjectData());
		    }
	    }
	        
	}

	/**
	 * @param string $subType is the bulk upload sub type
	 * @return int
	 */
	public function toSubType($subType)
	{
		if(is_null($subType))
			return null;
			
		return vPluginableEnumsManager::apiToCore('BulkUploadType', $subType);
	}
	
	/**
	 * @param int $subType
	 * @return string
	 */
	public function fromSubType($subType)
	{
		if(is_null($subType))
			return null;
			
		return vPluginableEnumsManager::coreToApi('BulkUploadType', $subType);
	}
	
	public function setType()
	{
	    
	}
}
