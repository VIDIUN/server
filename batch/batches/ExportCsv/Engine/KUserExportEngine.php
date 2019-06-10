<?php
/**
 * @package Scheduler
 * @subpackage ExportCsv
 */
class KUserExportEngine extends VObjectExportEngine
{
	
	public function fillCsv(&$csvFile, &$data)
	{
		VidiunLog::info ('Exporting content for user items');
		$filter = clone $data->filter;
		$pager = new VidiunFilterPager();
		$pager->pageSize = 500;
		$pager->pageIndex = 1;
		
		$additionalFields = $data->additionalFields;
		
		$this->addHeaderRowToCsv($csvFile, $additionalFields);
		$lastCreatedAtObjectIdList = array();
		$lastCreatedAt=0;
		$totalCount=0;
		$filter->orderBy = VidiunUserOrderBy::CREATED_AT_ASC;
		do
		{
			if($lastCreatedAt)
			{
				$filter->createdAtGreaterThanOrEqual = $lastCreatedAt;
			}
			try
			{
				$userList = VBatchBase::$vClient->user->listAction($filter, $pager);
				$returnedSize = $userList->objects ? count($userList->objects) : 0;
			}
			catch(Exception $e)
			{
				VidiunLog::info("Couldn't list users on page: [$pager->pageIndex]" . $e->getMessage());
				$this->apiError = $e;
				return;
			}
			
			$lastObject = $userList->objects[$returnedSize-1];
			$lastCreatedAt=$lastObject->createdAt;
			$newCreatedAtListObject = array();
			
			//contain only the users that are were not the former list
			$uniqUsers = array();
			foreach ($userList->objects as $user)
			{
				if(!in_array($user->id, $lastCreatedAtObjectIdList))
					$uniqUsers[]=$user;
			}
			//Prepare list of the last second users to avoid duplicate in the next iteration
			foreach ($uniqUsers as $user)
			{
				if($user->createdAt == $lastCreatedAt)
					$newCreatedAtListObject[]=$user->id;
			}
			$lastCreatedAtObjectIdList = $newCreatedAtListObject;
			$this->addUsersToCsv($uniqUsers, $csvFile, $data->metadataProfileId, $additionalFields);
			$totalCount+=count($uniqUsers);
			VidiunLog::debug("Adding More  - ".count($uniqUsers). " totalCount - ". $totalCount);
			unset($newCreatedAtListObject);
			unset($uniqUsers);
			unset($userList);
			if(function_exists('gc_collect_cycles')) // php 5.3 and above
				gc_collect_cycles();
		}
		while ($pager->pageSize == $returnedSize);
	}
	
	/**
	 * Generate the first csv row containing the fields
	 */
	protected function addHeaderRowToCsv($csvFile, $additionalFields)
	{
		$headerRow = 'User ID,First Name,Last Name,Email';
		foreach ($additionalFields as $field)
			$headerRow .= ','.$field->fieldName;
		VCsvWrapper::sanitizedFputCsv($csvFile, explode(',', $headerRow));
		
		return $csvFile;
	}
	
	/**
	 * The function grabs all the fields values for each user and adding them as a new row to the csv file
	 */
	protected function addUsersToCsv(&$users, &$csvFile, $metadataProfileId, $additionalFields)
	{
		if(!$users)
			return ;
		
		$userIds = array();
		$userIdToRow = array();
		
		foreach ($users as $user)
		{
			$userIds[] = $user->id;
			$userIdToRow = $this->initializeCsvRowValues($user, $additionalFields, $userIdToRow);
		}
		
		if($metadataProfileId)
		{
			$usersMetadata = $this->retrieveUsersMetadata($userIds, $metadataProfileId);
			if ($usersMetadata->objects)
				$userIdToRow = $this->fillAdditionalFieldsFromMetadata($usersMetadata, $additionalFields, $userIdToRow);
		}
		foreach ($userIdToRow as $key=>$val)
		{
			VCsvWrapper::sanitizedFputCsv($csvFile, $val);
		}
	}
	
	/**
	 * adds the default fields values and the additional fields as nulls
	 */
	protected function initializeCsvRowValues($user, $additionalFields, $userIdToRow)
	{
		$defaultRowValues = array(
			'id' => $user->id,
			'firstName' => $user->firstName,
			'lastName' => $user->lastName,
			'email' =>$user->email
		);
		
		$additionalKeys = array();
		foreach ($additionalFields as $field)
			$additionalKeys[] = $field->fieldName;
		$additionalRowValues = array_fill_keys($additionalKeys, null);
		$row = array_merge($defaultRowValues, $additionalRowValues);
		$userIdToRow[$user->id] = $row;
		
		return $userIdToRow;
	}
	
	
	
	/**
	 * Retrieve all the metadata objects for all the users in specific page
	 */
	protected function retrieveUsersMetadata($userIds, $metadataProfileId)
	{
		$result = null;
		$pager = new VidiunFilterPager();
		$pager->pageSize = 500;
		$pager->pageIndex = 1;
		$filter = new VidiunMetadataFilter();
		$filter->objectIdIn = implode(',', $userIds);
		$filter->metadataObjectTypeEqual = MetadataObjectType::USER;
		$filter->metadataProfileIdEqual = $metadataProfileId;
		try
		{
			$metadataClient = VidiunMetadataClientPlugin::get(VBatchBase::$vClient);
			$result = $metadataClient->metadata->listAction($filter, $pager);
		}
		catch(Exception $e)
		{
			VidiunLog::info("Couldn't list metadata objects for metadataProfileId: [$metadataProfileId]" . $e->getMessage());
			$this->apiError = $e;
		}
		return $result;
	}
	
	/**
	 * Extract specific value from xml using given xpath
	 */
	protected function getValueFromXmlElement($xml, $xpath)
	{
		$strValue = null;
		try
		{
			$xmlObj = new SimpleXMLElement($xml);
		}
		catch(Exception $ex)
		{
			return null;
		}
		$value = $xmlObj->xpath($xpath);
		if(is_array($value) && count($value) == 1)
			$strValue = (string)$value[0];
		else if (count($value) == 1)
			VidiunLog::err("Unknown element in the base xml when quering the xpath: [$xpath]");
		
		return $strValue;
	}
	
	/**
	 * the function run over each additional field and returns the value for the given field xpath
	 */
	private function fillAdditionalFieldsFromMetadata($usersMetadata, $additionalFields, $userIdToRow)
	{
		foreach($usersMetadata->objects as $metadataObj)
		{
			foreach ($additionalFields as $field)
			{
				if($field->xpath)
				{
					VidiunLog::info("current field xpath: [$field->xpath]");
					$strValue = $this->getValueFromXmlElement($metadataObj->xml, $field->xpath);
					if($strValue)
					{
						$objectRow = $userIdToRow[$metadataObj->objectId];
						$objectRow[$field->fieldName] = $strValue;
						$userIdToRow[$metadataObj->objectId] = $objectRow;
					}
				}
			}
		}
		return $userIdToRow;
	}
}