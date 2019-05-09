<?php
/**
 * @package Scheduler
 * @subpackage Bulk-Upload
 */
class DropFolderXmlBulkUploadEngine extends BulkUploadEngineXml
{
	/**
	 * @var VidiunDropFolder
	 */
	private $dropFolder = null;
	
	/**
	 * @var int VidiunDropFolderFileId
	 */
	private $xmlDropFolderFileId = null;
	
	/**
	 * @var vFileTransferMgr
	 */
	private $fileTransferMgr = null;
	
	/**
	 *
	 * @var array
	 */
	private $contentResourceNameToIdMap = null;
	
	/**
	 * XML provided VS info
	 * @var VidiunSessionInfo
	 */
	private $vsInfo = null;
	
	public function __construct(VidiunBatchJob $job)
	{
		parent::__construct($job);
		
		VBatchBase::impersonate($this->currentPartnerId);
		$dropFolderPlugin = VidiunDropFolderClientPlugin::get(VBatchBase::$vClient);
		$this->xmlDropFolderFileId = $this->job->jobObjectId;
		$this->dropFolder = $dropFolderPlugin->dropFolder->get($job->data->dropFolderId);
		$this->fileTransferMgr = VDropFolderFileTransferEngine::getFileTransferManager($this->dropFolder);
		if(!$this->data->filePath)
		{
			$xmlDropFolderFile = $dropFolderPlugin->dropFolderFile->get($this->xmlDropFolderFileId);
			$this->data->filePath = $this->getLocalFilePath($xmlDropFolderFile->fileName, $this->xmlDropFolderFileId);
		}
		
		VBatchBase::unimpersonate();
	}
	
	/* (non-PHPdoc)
	 * @see BulkUploadEngineXml::getSchemaType()
	 */
	protected function getSchemaType()
	{
		return VidiunSchemaType::DROP_FOLDER_XML;
	}
	
	/* (non-PHPdoc)
	 * @see BulkUploadEngineXml::handleBulkUpload()
	 */
	public function handleBulkUpload()
	{
		VBatchBase::impersonate($this->currentPartnerId);
		$dropFolderPlugin = VidiunDropFolderClientPlugin::get(VBatchBase::$vClient);
		$this->setContentResourceFilesMap($dropFolderPlugin);
		VBatchBase::unimpersonate();
		
		parent::handleBulkUpload();
	}
	
	private function setContentResourceFilesMap(VidiunDropFolderClientPlugin $dropFolderPlugin)
	{
		$filter = new VidiunDropFolderFileFilter();
		$filter->dropFolderIdEqual = $this->dropFolder->id;
		$filter->leadDropFolderFileIdEqual = $this->xmlDropFolderFileId;
		
		$pager = new VidiunFilterPager();
		$pager->pageSize = 500;
		$pager->pageIndex = 1;
		
		$getNextPage = true;
		
		$this->contentResourceNameToIdMap = array();
		
		while($getNextPage)
		{
			$dropFolderFiles = $dropFolderPlugin->dropFolderFile->listAction($filter, $pager);
			foreach ($dropFolderFiles->objects as $dropFolderFile)
			{
				$this->contentResourceNameToIdMap[$dropFolderFile->fileName] = $dropFolderFile->id;
			}
			
			if(count($dropFolderFiles->objects) < $pager->pageSize)
				$getNextPage = false;
			else
				$pager->pageIndex++;
		}
	}
	
	/* (non-PHPdoc)
	 * @see BulkUploadEngineXml::getResourceInstance()
	 */
	protected function getResourceInstance(SimpleXMLElement $elementToSearchIn, $conversionProfileId)
	{
		if(isset($elementToSearchIn->dropFolderFileContentResource))
		{
			$resource = new VidiunDropFolderFileResource();
			$attributes = $elementToSearchIn->dropFolderFileContentResource->attributes();
			$filePath = (string)$attributes['filePath'];
			$resource->dropFolderFileId = $this->contentResourceNameToIdMap[$filePath];
			
			return $resource;
		}
		
		return parent::getResourceInstance($elementToSearchIn, $conversionProfileId);
	}
	
	/* (non-PHPdoc)
	 * @see BulkUploadEngineXml::validateResource()
	 */
	protected function validateResource(VidiunResource $resource = null, SimpleXMLElement $elementToSearchIn)
	{
		if($resource instanceof VidiunDropFolderFileResource)
		{
			$fileId = $resource->dropFolderFileId;
			if (is_null($fileId)) {
				throw new VidiunBulkUploadXmlException("Drop folder id is null", VidiunBatchJobAppErrors::BULK_ITEM_VALIDATION_FAILED);
			}
						
			$filePath = $this->getFilePath($elementToSearchIn);
			$this->validateFileSize($elementToSearchIn, $filePath);
			if($this->dropFolder->type == VidiunDropFolderType::LOCAL)
			{
				$this->validateChecksum($elementToSearchIn, $filePath);
			}
		}
		
		return parent::validateResource($resource, $elementToSearchIn);
	}
	
	private function getFilePath(SimpleXMLElement $elementToSearchIn)
	{
		$attributes = $elementToSearchIn->dropFolderFileContentResource->attributes();
		$filePath = (string)$attributes['filePath'];
		
		if(isset($filePath))
		{
			$filePath = $this->dropFolder->path.'/'.$filePath;
			if($this->dropFolder->type == VidiunDropFolderType::LOCAL)
				$filePath = realpath($filePath);
			return $filePath;
		}
		else
		{
			throw new VidiunBulkUploadXmlException("Can't validate file as file path is null", VidiunBatchJobAppErrors::BULK_ITEM_VALIDATION_FAILED);
		}
	}
	
	private function validateFileSize(SimpleXMLElement $elementToSearchIn, $filePath)
	{
		if(isset($elementToSearchIn->dropFolderFileContentResource->fileSize))
		{
			$fileSize = $this->fileTransferMgr->fileSize($filePath);
			$xmlFileSize = (int)$elementToSearchIn->dropFolderFileContentResource->fileSize;
			if($xmlFileSize != $fileSize)
				throw new VidiunBulkUploadXmlException("File size is invalid for file [$filePath], Xml size [$xmlFileSize], actual size [$fileSize]", VidiunBatchJobAppErrors::BULK_ITEM_VALIDATION_FAILED);
		}
	}
	
	private function validateChecksum(SimpleXMLElement $elementToSearchIn, $filePath)
	{
		if(isset($elementToSearchIn->dropFolderFileContentResource->fileChecksum))
		{
			if($elementToSearchIn->dropFolderFileContentResource->fileChecksum['type'] == 'sha1')
			{
				 $checksum = sha1_file($filePath);
			}
			else
			{
				$checksum = md5_file($filePath);
			}
			
			$xmlChecksum = (string)$elementToSearchIn->dropFolderFileContentResource->fileChecksum;
			if($xmlChecksum != $checksum)
			{
				throw new VidiunBulkUploadXmlException("File checksum is invalid for file [$filePath], Xml checksum [$xmlChecksum], actual checksum [$checksum]", VidiunBatchJobAppErrors::BULK_ITEM_VALIDATION_FAILED);
			}
			VidiunLog::info("Checksum [$checksum] verified for local resource [$filePath]");
		}
	}
	
	/**
	 * Local drop folder - constract full path
	 * Remote drop folder - download file to a local temp directory and return the temp file path
	 * @param string $fileName
	 * @param int $fileId
	 * @throws Exception
	 */
	protected function getLocalFilePath($fileName, $fileId)
	{
		$dropFolderFilePath = $this->dropFolder->path.'/'.$fileName;
	    
	    // local drop folder
	    if ($this->dropFolder->type == VidiunDropFolderType::LOCAL) 
	    {
	        $dropFolderFilePath = realpath($dropFolderFilePath);
	        return $dropFolderFilePath;
	    }
	    else
	    {
	    	// remote drop folder	
			$tempFilePath = tempnam(VBatchBase::$taskConfig->params->sharedTempPath, 'parse_dropFolderFileId_'.$fileId.'_');		
			$this->fileTransferMgr->getFile($dropFolderFilePath, $tempFilePath);
			$this->setFilePermissions ($tempFilePath);
			return $tempFilePath;
	    }			    		
	}
	
	protected function setFilePermissions ($filepath)
	{
		$chmod = 0640;
		if(VBatchBase::$taskConfig->getChmod())
			$chmod = octdec(VBatchBase::$taskConfig->getChmod());
			
		VidiunLog::info("chmod($filepath, $chmod)");
		@chmod($filepath, $chmod);
		
		$chown_name = VBatchBase::$taskConfig->params->fileOwner;
		if ($chown_name) {
			VidiunLog::info("Changing owner of file [$filepath] to [$chown_name]");
			@chown($filepath, $chown_name);
		}
	}
	
	protected function validate()
	{
		$isValid = parent::validate();
		
		if($this->dropFolder->shouldValidateVS){
			$this->validateVs();		
		}
		
		return $isValid;
	}
	
	protected function validateVs()
	{
		//Retrieve the VS from within the XML
		$xdoc = new SimpleXMLElement($this->xslTransformedContent);
		$xmlVs = $xdoc->vs;
		
		//Get session info
		VBatchBase::impersonate($this->currentPartnerId);
		try{
			$this->vsInfo = VBatchBase::$vClient->session->get($xmlVs);	
		}
		catch (Exception $e){
			VBatchBase::unimpersonate();
			throw new VidiunBatchException("VS [$xmlVs] validation failed for [{$this->job->id}], $errorMessage", VidiunBatchJobAppErrors::BULK_VALIDATION_FAILED);
		}
		VBatchBase::unimpersonate();
		
		//validate vs is still valid
		$currentTime = time();
		if($currentTime > $this->vsInfo->expiry){
			throw new VidiunBatchException("VS validation failed for [{$this->job->id}], vs provided in XML Expired", VidiunBatchJobAppErrors::BULK_VALIDATION_FAILED);
		}
	}
	
	/**
	 * Validates the given item's user id is identical to the user id on the VS
	 * @param SimpleXMLElement $item
	 */
	protected function validateItem(SimpleXMLElement $item)
	{
		if($this->dropFolder->shouldValidateVS){
			if(!isset($item->userId) && $this->vsInfo->sessionType == VidiunSessionType::USER)
				throw new VidiunBulkUploadXmlException("Drop Folder is set with VS validation but no user id was provided", VidiunBatchJobAppErrors::BULK_ITEM_VALIDATION_FAILED);
			if($item->userId != $this->vsInfo->userId && $this->vsInfo->sessionType == VidiunSessionType::USER)
				throw new VidiunBulkUploadXmlException("Drop Folder is set with VS validation, VS user ID [" . $this->vsInfo->userId . "] does not match item user ID [" . $item->userId . "]", VidiunBatchJobAppErrors::BULK_ITEM_VALIDATION_FAILED);
		}
			
		parent::validateItem($item);
	}
	
	protected function createEntryFromItem(SimpleXMLElement $item, $type = null)
	{
		$entry = parent::createEntryFromItem($item, $type);
		
		if($this->dropFolder->shouldValidateVS && !isset($entry->userId))
			$entry->userId = $this->vsInfo->userId;
			
		return $entry;
	}
}