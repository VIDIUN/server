<?php
/**
 * Class which parses the bulk upload CSV and creates the objects listed in it.
 * This engine class parses CSVs which describe entries.
 *
 * @package plugins.bulkUploadCsv
 * @subpackage batch
 */
class BulkUploadEntryEngineCsv extends BulkUploadEngineCsv
{
	/**
	 * The column count (values) for the V1 CSV format
	 * @var int
	 */
	const VALUES_COUNT_V1 = 5;

	/**
	 * The column count (values) for the V1 CSV format
	 * @var int
	 */
	const VALUES_COUNT_V2 = 12;

	const OBJECT_TYPE_TITLE = 'entry';

	/* (non-PHPdoc)
	 * @see VBulkUploadEngine::addBulkUploadResult()
	 */
	protected function addBulkUploadResult(VidiunBulkUploadResult $bulkUploadResult)
	{
		parent::addBulkUploadResult($bulkUploadResult);

		if(($bulkUploadResult->entryId || $bulkUploadResult->objectId) && $bulkUploadResult->entryStatus == VidiunEntryStatus::IMPORT)
		{
		    $url = $bulkUploadResult->url;
		    $isSsh = (stripos($url, 'sftp:') === 0) || (stripos($url, 'scp:') === 0);
		    if ($isSsh) {
		        $resource = new VidiunSshUrlResource();
		        $resource->privateKey = $bulkUploadResult->sshPrivateKey;
		        $resource->publicKey = $bulkUploadResult->sshPublicKey;
		        $resource->keyPassphrase = $bulkUploadResult->sshKeyPassphrase;
		    }
		    else {
		        $resource = new VidiunUrlResource();
		    }
			$resource->url = $url;
			$resource->forceAsyncDownload = true;

			VBatchBase::impersonate($this->currentPartnerId);;
			VBatchBase::$vClient->media->addContent($bulkUploadResult->entryId, $resource);
			VBatchBase::unimpersonate();
		}
	}

	/**
	 *
	 * Create the entries from the given bulk upload results
	 */
	protected function createObjects()
	{
		// start a multi request for add entries
		VBatchBase::$vClient->startMultiRequest();

		VidiunLog::info("job[{$this->job->id}] start creating entries [" . count($this->bulkUploadResults) . "]");
		$bulkUploadResultChunk = array(); // store the results of the created entries

		foreach($this->bulkUploadResults as $bulkUploadResult)
		{
		    /* @var $bulkUploadResult VidiunBulkUploadResult */
		    switch ($bulkUploadResult->action)
		    {
		        case VidiunBulkUploadAction::ADD:
    		        $mediaEntry = $this->createMediaEntryFromResultAndJobData($bulkUploadResult);

        			$bulkUploadResultChunk[] = $bulkUploadResult;

        			VBatchBase::impersonate($this->currentPartnerId);;
        			VBatchBase::$vClient->media->add($mediaEntry);
        			VBatchBase::unimpersonate();

        			if(VBatchBase::$vClient->getMultiRequestQueueSize() >= $this->multiRequestSize)
        			{
        				// make all the media->add as the partner
        				$requestResults = VBatchBase::$vClient->doMultiRequest();

        				$this->updateObjectsResults($requestResults, $bulkUploadResultChunk);
        				$this->checkAborted();
        				VBatchBase::$vClient->startMultiRequest();
        				$bulkUploadResultChunk = array();
        			}
		            break;

		        case VidiunBulkUploadAction::UPDATE:
		            break;

		        case VidiunBulkUploadAction::DELETE:
		            break;

		        default:
		            $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
		            $bulkUploadResult->errorDescription = "unknown action passed: [".$bulkUploadResult->action ."]";
		            break;
		    }

		}

		// make all the media->add as the partner
		$requestResults = VBatchBase::$vClient->doMultiRequest();

		if(count($requestResults))
			$this->updateObjectsResults($requestResults, $bulkUploadResultChunk);

		VidiunLog::info("job[{$this->job->id}] finish creating entries");
	}

	/**
	 *
	 * Creates and returns a new media entry for the given job data and bulk upload result object
	 * @param VidiunBulkUploadResultEntry $bulkUploadResult
	 */
	protected function createMediaEntryFromResultAndJobData($bulkUploadResult)
	{
		//Create the new media entry and set basic values
		$mediaEntry = new VidiunMediaEntry();
		$mediaEntry->name = $bulkUploadResult->title;
		$mediaEntry->description = $bulkUploadResult->description;
		$mediaEntry->tags = $bulkUploadResult->tags;
		$mediaEntry->userId = $this->data->userId;
		$mediaEntry->creatorId = $this->data->userId;
		$mediaEntry->conversionProfileId = $this->data->objectData->conversionProfileId;

		//Set values for V1 csv
		if($this->csvVersion > VidiunBulkUploadCsvVersion::V1)
		{
			if($bulkUploadResult->conversionProfileId)
		    	$mediaEntry->conversionProfileId = $bulkUploadResult->conversionProfileId;

			if($bulkUploadResult->accessControlProfileId)
		    	$mediaEntry->accessControlId = $bulkUploadResult->accessControlProfileId;

		    if($bulkUploadResult->scheduleStartDate)
		    	$mediaEntry->startDate = $bulkUploadResult->scheduleStartDate;

		    if($bulkUploadResult->scheduleEndDate)
		    	$mediaEntry->endDate = $bulkUploadResult->scheduleEndDate;

		    if($bulkUploadResult->partnerData)
		    	$mediaEntry->partnerData = $bulkUploadResult->partnerData;

		    if($bulkUploadResult->entitledUsersEdit)
		    	$mediaEntry->entitledUsersEdit = $bulkUploadResult->entitledUsersEdit;

		    if($bulkUploadResult->entitledUsersPublish)
		    	$mediaEntry->entitledUsersPublish = $bulkUploadResult->entitledUsersPublish;

		    if($bulkUploadResult->referenceId)
		    	$mediaEntry->referenceId = $bulkUploadResult->referenceId;
		    	
		    if($bulkUploadResult->templateEntryId)
		    {
		    	$mediaEntry->templateEntryId = $bulkUploadResult->templateEntryId;
		    	$mediaEntry->userId = null;
		    }
		    
		    if($bulkUploadResult->ownerId)
		    	$mediaEntry->userId = $bulkUploadResult->ownerId;
		}

		//Set the content type
		switch(strtolower($bulkUploadResult->contentType))
		{
			case 'image':
				$mediaEntry->mediaType = VidiunMediaType::IMAGE;
				break;

			case 'audio':
				$mediaEntry->mediaType = VidiunMediaType::AUDIO;
				break;

			default:
				$mediaEntry->mediaType = VidiunMediaType::VIDEO;
				break;
		}

		return $mediaEntry;
	}

	/**
	 *
	 * Creates a new upload result object from the given parameters
	 * @param array $values
	 * @param array $columns
	 */
	protected function createUploadResult($values, $columns)
	{
	    $bulkUploadResult = parent::createUploadResult($values, $columns);
	    if (!$bulkUploadResult)
	    	return;

		$bulkUploadResult->bulkUploadResultObjectType = VidiunBulkUploadObjectType::ENTRY;

		// Check variables count
		if($this->csvVersion != VidiunBulkUploadCsvVersion::V3)
		{
			if(count($values) == self::VALUES_COUNT_V1)
			{
				$this->csvVersion = VidiunBulkUploadCsvVersion::V1;
				$columns = $this->getV1Columns();
			}
			elseif(count($values) == self::VALUES_COUNT_V2)
			{
				$this->csvVersion = VidiunBulkUploadCsvVersion::V2;
				$columns = $this->getV2Columns();
			}
			else
			{
				// fail and continue with next line
				$bulkUploadResult->entryStatus = VidiunEntryStatus::ERROR_IMPORTING;
				$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
				$bulkUploadResult->errorDescription = "Wrong number of values on line $this->lineNumber";
				$this->addBulkUploadResult($bulkUploadResult);
				return;
			}
			VidiunLog::info("Columns:\n" . print_r($columns, true));
		}

		// trim the values
		array_walk($values, array('BulkUploadEntryEngineCsv', 'trimArray'));

	    $scheduleStartDate = null;
	    $scheduleEndDate = null;

		// sets the result values
		foreach($columns as $index => $column)
		{
			if(!is_numeric($index))
				continue;

			if($column == 'scheduleStartDate' || $column == 'scheduleEndDate')
			{
				$$column = strlen($values[$index]) ? $values[$index] : null;
				VidiunLog::info("Set value \${$column} [{$$column}]");
			}
			else if ($column == 'entryId')
			{
			    $bulkUploadResult->objectId = $values[$index];
			}
			else
			{
				if(iconv_strlen($values[$index], 'UTF-8'))
				{
					$bulkUploadResult->$column = $values[$index];
					VidiunLog::info("Set value $column [{$bulkUploadResult->$column}]");
				}
				else
				{
					VidiunLog::info("Value $column is empty");
				}
			}
		}

		if(isset($columns['plugins']))
		{
			$bulkUploadPlugins = array();

			foreach($columns['plugins'] as $index => $column)
			{
				$bulkUploadPlugin = new VidiunBulkUploadPluginData();
				$bulkUploadPlugin->field = $column;
				$bulkUploadPlugin->value = iconv_strlen($values[$index], 'UTF-8') ? $values[$index] : null;
				$bulkUploadPlugins[] = $bulkUploadPlugin;

				VidiunLog::info("Set plugin value $column [{$bulkUploadPlugin->value}]");
			}

			$bulkUploadResult->pluginsData = $bulkUploadPlugins;
		}

		$bulkUploadResult->entryStatus = VidiunEntryStatus::IMPORT;
		$bulkUploadResult->status = VidiunBulkUploadResultStatus::IN_PROGRESS;

		if (!$bulkUploadResult->action)
		{
		    $bulkUploadResult->action = VidiunBulkUploadAction::ADD;
		}

		if(!is_numeric($bulkUploadResult->conversionProfileId))
			$bulkUploadResult->conversionProfileId = null;

		if(!is_numeric($bulkUploadResult->accessControlProfileId))
			$bulkUploadResult->accessControlProfileId = null;

		if($this->maxRecords && $this->lineNumber > $this->maxRecords) // check max records
		{
			$bulkUploadResult->entryStatus = VidiunEntryStatus::ERROR_IMPORTING;
			$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorDescription = "Exeeded max records count per bulk";
		}

		if(!$this->isUrl($bulkUploadResult->url)) // validates the url
		{
			$bulkUploadResult->entryStatus = VidiunEntryStatus::ERROR_IMPORTING;
			$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorDescription = "Invalid url '$bulkUploadResult->url' on line $this->lineNumber";
		}

		if($scheduleStartDate && !self::isFormatedDate($scheduleStartDate))
		{
			$bulkUploadResult->entryStatus = VidiunEntryStatus::ERROR_IMPORTING;
			$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorDescription = "Invalid schedule start date '$scheduleStartDate' on line $this->lineNumber";
		}

		if($scheduleEndDate && !self::isFormatedDate($scheduleEndDate))
		{
			$bulkUploadResult->entryStatus = VidiunEntryStatus::ERROR_IMPORTING;
			$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorDescription = "Invalid schedule end date '$scheduleEndDate' on line $this->lineNumber";
		}

	    $privateKey = isset($bulkUploadResult->sshPrivateKey) ? $bulkUploadResult->sshPrivateKey : false;
		$publicKey = isset($bulkUploadResult->sshPublicKey) ? $bulkUploadResult->sshPublicKey : false;

		if (empty($privateKey) & !empty($publicKey)) {
		    $bulkUploadResult->entryStatus = VidiunEntryStatus::ERROR_IMPORTING;
		    $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorDescription = "Missing SSH private key on line  $this->lineNumber";

		}
		else if (!empty($privateKey) & empty($publicKey)) {
		    $bulkUploadResult->entryStatus = VidiunEntryStatus::ERROR_IMPORTING;
		    $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorDescription = "Missing SSH public key on line $this->lineNumber";
		}

		if($bulkUploadResult->status == VidiunBulkUploadResultStatus::ERROR)
		{
			$this->addBulkUploadResult($bulkUploadResult);
			return;
		}

		$bulkUploadResult->scheduleStartDate = self::parseFormatedDate($scheduleStartDate);
		$bulkUploadResult->scheduleEndDate = self::parseFormatedDate($scheduleEndDate);

		$this->bulkUploadResults[] = $bulkUploadResult;
	}

	/**
	 *
	 * Gets the columns for V1 csv file
	 */
	protected function getV1Columns()
	{
		return array(
			'title',
			'description',
			'tags',
			'url',
			'contentType',
		);
	}

	/**
	 *
	 * Gets the columns for V2 csv file
	 */
	protected function getV2Columns()
	{
		$ret = $this->getV1Columns();

		$ret[] = 'conversionProfileId';
	    $ret[] = 'accessControlProfileId';
	    $ret[] = 'category';
		$ret[] = 'scheduleStartDate';
		$ret[] = 'scheduleEndDate';
	    $ret[] = 'thumbnailUrl';
	    $ret[] = 'partnerData';
	    $ret[] = 'sshPrivateKey';
	    $ret[] = 'sshPublicKey';
	    $ret[] = 'sshKeyPassphrase';

	    return $ret;
	}

	protected function getColumns()
	{
	    $ret = $this->getV2Columns();
	    $ret[] = 'entryId';
	    $ret[] = 'action';
	    $ret[] = 'ownerId';
	    $ret[] = 'entitledUsersEdit';
	    $ret[] = 'entitledUsersPublish';
	    $ret[] = 'referenceId';
	    $ret[] = 'templateEntryId';

	    return $ret;
	}


	protected function updateObjectsResults(array $requestResults, array $bulkUploadResults)
	{
		VidiunLog::info("Updating " . count($requestResults) . " results");

		// checking the created entries
		foreach($requestResults as $index => $requestResult)
		{
		    /* @var $bulkUploadResult VidiunBulkUploadResultEntry */
			$bulkUploadResult = $bulkUploadResults[$index];

			if(is_array($requestResult) && isset($requestResult['code']))
			{
			    $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			    $bulkUploadResult->errorType = VidiunBatchJobErrorTypes::VIDIUN_API;
				$bulkUploadResult->entryStatus = $requestResult['code'];
				$bulkUploadResult->errorDescription = $requestResult['message'];
				$this->addBulkUploadResult($bulkUploadResult);
				continue;
			}

			if($requestResult instanceof Exception)
			{
				$bulkUploadResult->entryStatus = VidiunEntryStatus::ERROR_IMPORTING;
				$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
				$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::VIDIUN_API;
				$bulkUploadResult->errorDescription = $requestResult->getMessage();
				$this->addBulkUploadResult($bulkUploadResult);
				continue;
			}

			if(! ($requestResult instanceof VidiunBaseEntry))
			{
				$bulkUploadResult->entryStatus = VidiunEntryStatus::ERROR_IMPORTING;
				$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
				$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::VIDIUN_API;
				$bulkUploadResult->errorDescription = "Returned type is " . get_class($requestResult) . ', VidiunMediaEntry was expected';
				$this->addBulkUploadResult($bulkUploadResult);
				continue;
			}

			// update the results with the new entry id
			$bulkUploadResult->entryId = $requestResult->id;
			$bulkUploadResult->objectId = $requestResult->id;
			$this->createCategoryAssociations($bulkUploadResult->entryId, $bulkUploadResult->category, $bulkUploadResult);
			$this->addBulkUploadResult($bulkUploadResult);
		}

	}

	/**
	 * Function which creates VidiunCategoryEntry objects for the entry which was added
	 * via the bulk upload CSV.
	 * @param string $entryId
	 * @param string $categories
	 * @param VidiunBulkUploadResultEntry $bulkuploadResult
	 */
	private function createCategoryAssociations ($entryId, $categories, VidiunBulkUploadResultEntry $bulkuploadResult)
	{
		if(!$categories) {	// skip this prcoess if no categories are present
			VidiunLog::notice("No categories found for entry ID [$entryId], skipping association creating");
			return;
		}
	    VBatchBase::impersonate($this->currentPartnerId);;

	    $categoriesArr = explode(",", $categories);
	    $ret = array();
	    foreach ($categoriesArr as $categoryName)
	    {
	        $categoryFilter = new VidiunCategoryFilter();
	        $categoryFilter->fullNameEqual = $categoryName;
	        $res = VBatchBase::$vClient->category->listAction($categoryFilter, new VidiunFilterPager());
	        if (!count($res->objects))
	        {
	           $res = $this->createCategoryByPath($categoryName);
	           if (! $res instanceof  VidiunCategory)
	           {
	               $bulkuploadResult->errorDescription .= $res;
	               continue;
	           }

	           $category = $res;
	        }
	        else
	        {
	            $category = $res->objects[0];
	        }
	        $categoryEntry = new VidiunCategoryEntry();
	        $categoryEntry->categoryId = $category->id;
	        $categoryEntry->entryId = $entryId;
	        try {
	            VBatchBase::$vClient->categoryEntry->add($categoryEntry);
	        }
	        catch (Exception $e)
	        {
	            $bulkuploadResult->errorDescription .= $e->getMessage();
	        }
	    }

	    VBatchBase::unimpersonate();
	    return;
	}

	private function createCategoryByPath ($fullname)
	{
        $catNames = explode(">", $fullname);
        $parentId = null;
        $fullNameEq = '';
        foreach ($catNames as $catName)
        {
            $category = new VidiunCategory();
            $category->name = $catName;
            $category->parentId = $parentId;

            if ($fullNameEq == '')
            	$fullNameEq .= $catName;
            else
            	$fullNameEq .= ">$catName";

            try
            {
                $category = VBatchBase::$vClient->category->add($category);
            }
            catch (Exception $e)
            {
                if ($e->getCode() == 'DUPLICATE_CATEGORY')
                {
                    $catFilter = new VidiunCategoryFilter();
                    $catFilter->fullNameEqual = $fullNameEq;
                    $res = VBatchBase::$vClient->category->listAction($catFilter);
                    $category = $res->objects[0];
                }
                else
                {
                    return $e->getMessage();
                }
            }

            $parentId = $category->id;
        }

        return $category;

	}

	protected function getUploadResultInstance ()
	{
	    return new VidiunBulkUploadResultEntry();
	}

	public function getObjectTypeTitle()
	{
		return self::OBJECT_TYPE_TITLE;
	}
}
