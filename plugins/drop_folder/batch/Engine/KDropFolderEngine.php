<?php
/**
 * 
 */
abstract class VDropFolderEngine implements IVidiunLogger
{
	protected $dropFolder;
	
	protected $dropFolderPlugin;
	
	protected $dropFolderFileService;

	private $maximumExecutionTime = null;
	
	public function __construct ()
	{
		$this->dropFolderPlugin = VidiunDropFolderClientPlugin::get(VBatchBase::$vClient);
		$this->dropFolderFileService = $this->dropFolderPlugin->dropFolderFile;
	}
	
	public static function getInstance ($dropFolderType)
	{
		switch ($dropFolderType) {
			case VidiunDropFolderType::FTP:
			case VidiunDropFolderType::SFTP:
			case VidiunDropFolderType::LOCAL:
				return new VDropFolderFileTransferEngine ();
				break;
			
			default:
				return VidiunPluginManager::loadObject('VDropFolderEngine', $dropFolderType);
				break;
		}
	}
	
	abstract public function watchFolder (VidiunDropFolder $dropFolder);
	
	abstract public function processFolder (VidiunBatchJob $job, VidiunDropFolderContentProcessorJobData $data);

	/**
	 * Load all the files from the database that their status is not PURGED, PARSED or DETECTED
	 * @param VidiunFilterPager $pager
	 * @return array
	 */
	protected function loadDropFolderFilesByPage($pager)
	{
		$dropFolderFiles =null;

		$dropFolderFileFilter = new VidiunDropFolderFileFilter();
		$dropFolderFileFilter->dropFolderIdEqual = $this->dropFolder->id;
		$dropFolderFileFilter->statusNotIn = VidiunDropFolderFileStatus::PARSED.','.VidiunDropFolderFileStatus::DETECTED;
		$dropFolderFileFilter->orderBy = VidiunDropFolderFileOrderBy::CREATED_AT_ASC;

		$dropFolderFiles = $this->dropFolderFileService->listAction($dropFolderFileFilter, $pager);
		return $dropFolderFiles->objects;
	}

	/**
	 * Load all the files from the database that their status is not PURGED, PARSED or DETECTED
	 * @return array
	 */
	protected function loadDropFolderFiles()
	{
		$dropFolderFiles =null;

		$dropFolderFileFilter = new VidiunDropFolderFileFilter();
		$dropFolderFileFilter->dropFolderIdEqual = $this->dropFolder->id;
		$dropFolderFileFilter->statusNotIn = VidiunDropFolderFileStatus::PARSED.','.VidiunDropFolderFileStatus::DETECTED;
		$dropFolderFileFilter->orderBy = VidiunDropFolderFileOrderBy::CREATED_AT_ASC;

		$pager = new VidiunFilterPager();
		$pager->pageSize = 500;
		if(VBatchBase::$taskConfig && VBatchBase::$taskConfig->params->pageSize)
			$pager->pageSize = VBatchBase::$taskConfig->params->pageSize;

		return $this->loadDropFolderFilesMap($dropFolderFileFilter, $pager);
	}

	/**
	 * Load all the files from the database that their status is UPLOADING and updatedAt LessThan Or Equal $updatedAt
	 * @param $updatedAt time
	 * @return array
	 */
	protected function loadDropFolderUpLoadingFiles($updatedAt)
	{
		$dropFolderFiles =null;

		$dropFolderFileFilter = new VidiunDropFolderFileFilter();
		$dropFolderFileFilter->dropFolderIdEqual = $this->dropFolder->id;
		$dropFolderFileFilter->statusEqual = VidiunDropFolderFileStatus::UPLOADING;
		$dropFolderFileFilter->updatedAtLessThanOrEqual = $updatedAt;
		$dropFolderFileFilter->orderBy = VidiunDropFolderFileOrderBy::CREATED_AT_ASC;

		$pager = new VidiunFilterPager();
		$pager->pageSize = 500;
		if(VBatchBase::$taskConfig && VBatchBase::$taskConfig->params->pageSize)
			$pager->pageSize = VBatchBase::$taskConfig->params->pageSize;

		return $this->loadDropFolderFilesMap($dropFolderFileFilter, $pager);
	}

	/**
	 * @param $dropFolderFileFilter VidiunDropFolderFileFilter
	 * @param $pager VidiunFilterPager
	 * @return array
	 */
	protected function loadDropFolderFilesMap($dropFolderFileFilter, $pager)
	{
		$dropFolderFilesMap = array();
		$totalCount = 0;
		do
		{
			$pager->pageIndex++;
			$dropFolderFiles = $this->dropFolderFileService->listAction($dropFolderFileFilter, $pager);
			if (!$totalCount)
			{
				$totalCount = $dropFolderFiles->totalCount;
			}

			$dropFolderFiles = $dropFolderFiles->objects;
			foreach ($dropFolderFiles as $dropFolderFile)
			{
				$dropFolderFilesMap[$dropFolderFile->fileName] = $dropFolderFile;
			}

		} while (count($dropFolderFiles) >= $pager->pageSize);

		$mapCount = count($dropFolderFilesMap);
		VidiunLog::debug("Drop folder [" . $this->dropFolder->id . "] has [$totalCount] file");
		if ($totalCount != $mapCount)
		{
			VidiunLog::warning("Map is missing files - Drop folder [" . $this->dropFolder->id . "] has [$totalCount] file from list BUT has [$mapCount] files in map");
		}

		return $dropFolderFilesMap;
	}

	/**
	 * Update drop folder entity with error
	 * @param int $dropFolderFileId
	 * @param int $errorStatus
	 * @param int $errorCode
	 * @param string $errorMessage
	 * @param Exception $e
	 */
	protected function handleFileError($dropFolderFileId, $errorStatus, $errorCode, $errorMessage, Exception $e = null)
	{
		try 
		{
			if($e)
				VidiunLog::err('Error for drop folder file with id ['.$dropFolderFileId.'] - '.$e->getMessage());
			else
				VidiunLog::err('Error for drop folder file with id ['.$dropFolderFileId.'] - '.$errorMessage);
			
			$updateDropFolderFile = new VidiunDropFolderFile();
			$updateDropFolderFile->errorCode = $errorCode;
			$updateDropFolderFile->errorDescription = $errorMessage;
			$this->dropFolderFileService->update($dropFolderFileId, $updateDropFolderFile);
			return $this->dropFolderFileService->updateStatus($dropFolderFileId, $errorStatus);				
		}
		catch (VidiunException $e) 
		{
			VidiunLog::err('Cannot set error details for drop folder file id ['.$dropFolderFileId.'] - '.$e->getMessage());
			return null;
		}
	}
	
	/**
	 * Mark file status as PURGED
	 * @param int $dropFolderFileId
	 */
	protected function handleFilePurged($dropFolderFileId)
	{
		try 
		{
			return $this->dropFolderFileService->updateStatus($dropFolderFileId, VidiunDropFolderFileStatus::PURGED);
		}
		catch(Exception $e)
		{
			$this->handleFileError($dropFolderFileId, VidiunDropFolderFileStatus::ERROR_HANDLING, VidiunDropFolderFileErrorCode::ERROR_UPDATE_FILE, 
									DropFolderPlugin::ERROR_UPDATE_FILE_MESSAGE, $e);
			
			return null;
		}		
	}
	
	/**
	 * Retrieve all the relevant drop folder files according to the list of id's passed on the job data.
	 * Create resource object based on the conversion profile as an input to the ingestion API
	 * @param VidiunBatchJob $job
	 * @param VidiunDropFolderContentProcessorJobData $data
	 */
	protected function getIngestionResource(VidiunBatchJob $job, VidiunDropFolderContentProcessorJobData $data)
	{
		$filter = new VidiunDropFolderFileFilter();
		$filter->idIn = $data->dropFolderFileIds;
		$dropFolderFiles = $this->dropFolderFileService->listAction($filter); 
		
		$resource = null;
		if($dropFolderFiles->totalCount == 1 && is_null($dropFolderFiles->objects[0]->parsedFlavor)) //only source is ingested
		{
			$resource = new VidiunDropFolderFileResource();
			$resource->dropFolderFileId = $dropFolderFiles->objects[0]->id;			
		}
		else //ingest all the required flavors
		{			
			$fileToFlavorMap = array();
			foreach ($dropFolderFiles->objects as $dropFolderFile) 
			{
				$fileToFlavorMap[$dropFolderFile->parsedFlavor] = $dropFolderFile->id;			
			}
			
			$assetContainerArray = array();
		
			$assetParamsFilter = new VidiunConversionProfileAssetParamsFilter();
			$assetParamsFilter->conversionProfileIdEqual = $data->conversionProfileId;
			$assetParamsList = VBatchBase::$vClient->conversionProfileAssetParams->listAction($assetParamsFilter);
			foreach ($assetParamsList->objects as $assetParams)
			{
				if(array_key_exists($assetParams->systemName, $fileToFlavorMap))
				{
					$assetContainer = new VidiunAssetParamsResourceContainer();
					$assetContainer->assetParamsId = $assetParams->assetParamsId;
					$assetContainer->resource = new VidiunDropFolderFileResource();
					$assetContainer->resource->dropFolderFileId = $fileToFlavorMap[$assetParams->systemName];
					$assetContainerArray[] = $assetContainer;				
				}			
			}		
			$resource = new VidiunAssetsParamsResourceContainers();
			$resource->resources = $assetContainerArray;
		}
		return $resource;		
	}

	protected function createCategoryAssociations (VidiunDropFolder $folder, $userId, $entryId)
	{
		if ($folder->metadataProfileId && $folder->categoriesMetadataFieldName)
		{
			$filter = new VidiunMetadataFilter();
			$filter->metadataProfileIdEqual = $folder->metadataProfileId;
			$filter->objectIdEqual = $userId;
			$filter->metadataObjectTypeEqual = VidiunMetadataObjectType::USER;
			
			try
			{
				$metadataPlugin = VidiunMetadataClientPlugin::get(VBatchBase::$vClient);
				//Expect only one result
				$res = $metadataPlugin->metadata->listAction($filter, new KalturaFilterPager());
				
				if(!$res->objects || !count($res->objects))
					return;
				
				$metadataObj = $res->objects[0];
				$xmlElem = new SimpleXMLElement($metadataObj->xml);
				$categoriesXPathRes = $xmlElem->xpath($folder->categoriesMetadataFieldName);
				$categories = array();
				foreach ($categoriesXPathRes as $catXPath)
				{
					$categories[] = strval($catXPath);
				}
				
				$categoryFilter = new VidiunCategoryFilter();
				$categoryFilter->idIn = implode(',', $categories);
				$categoryListResponse = VBatchBase::$vClient->category->listAction ($categoryFilter, new VidiunFilterPager());
				if ($categoryListResponse->objects && count($categoryListResponse->objects))
				{
					if (!$folder->enforceEntitlement)
					{
						//easy
						$this->createCategoryEntriesNoEntitlement ($categoryListResponse->objects, $entryId);
					}
					else {
						//write your will
						$this->createCategoryEntriesWithEntitlement ($categoryListResponse->objects, $entryId, $userId);
					}
				}
			}
			catch (Exception $e)
			{
				VidiunLog::err('Error encountered. Code: ['. $e->getCode() . '] Message: [' . $e->getMessage() . ']');
			}
		}
	}

	private function createCategoryEntriesNoEntitlement (array $categoriesArr, $entryId)
	{
		VBatchBase::$vClient->startMultiRequest();
		foreach ($categoriesArr as $category)
		{
			$categoryEntry = new VidiunCategoryEntry();
			$categoryEntry->entryId = $entryId;
			$categoryEntry->categoryId = $category->id;
			VBatchBase::$vClient->categoryEntry->add($categoryEntry);
		}
		VBatchBase::$vClient->doMultiRequest();
	}
	
	private function createCategoryEntriesWithEntitlement (array $categoriesArr, $entryId, $userId)
	{
		$partnerInfo = VBatchBase::$vClient->partner->get(VBatchBase::$vClientConfig->partnerId);
		
		$clientConfig = new VidiunConfiguration($partnerInfo->id);
		$clientConfig->serviceUrl = VBatchBase::$vClient->getConfig()->serviceUrl;
		$clientConfig->setLogger($this);
		$client = new VidiunClient($clientConfig);
		foreach ($categoriesArr as $category)
		{
			/* @var $category VidiunCategory */
			$vs = $client->generateSessionV2($partnerInfo->adminSecret, $userId, VidiunSessionType::ADMIN, $partnerInfo->id, 86400, 'enableentitlement,privacycontext:'.$category->privacyContexts);
			$client->setVs($vs);
			$categoryEntry = new VidiunCategoryEntry();
			$categoryEntry->categoryId = $category->id;
			$categoryEntry->entryId = $entryId;
			try
			{
				$client->categoryEntry->add ($categoryEntry);
			}
			catch (Exception $e)
			{
				VidiunLog::err("Could not add entry $entryId to category {$category->id}. Exception thrown.");
			}
		}
	}
	
	function log($message)
	{
		VidiunLog::log($message);
	}
	
	public function setMaximumExecutionTime($maximumExecutionTime = null)
	{
		if (is_null($this->maximumExecutionTime))
			$this->maximumExecutionTime = $maximumExecutionTime;
	}

	public function getMaximumExecutionTime()
	{
		return $this->maximumExecutionTime;
	}
}
