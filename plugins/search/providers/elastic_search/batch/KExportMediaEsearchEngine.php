<?php
/**
 * @package Scheduler
 * @subpackage ExportCsv
 */

class VExportMediaEsearchEngine extends VObjectExportEngine
{
	
	const LIMIT = 10000;
	
	const PAGE_SIZE = 500;
	
	public function fillCsv(&$csvFile, &$data)
	{
		VidiunLog::info ('Exporting content for media items through Esearch');
		$entrySearchParams = clone $data->searchParams;
		
		$results = VidiunElasticSearchClientPlugin::get(VBatchBase::$vClient)->eSearch->searchEntry($entrySearchParams);
		if ($results->totalCount > self::LIMIT)
		{
			VidiunLog::info ('More than 10000 results detected. Only the first 10000 results will be returned.');
		}
		
		// TODO: at this point, no additional fields are allowed to be passed
		$this->addHeaderRowToCsv($csvFile, array());
		
		$entryPager = new VidiunFilterPager();
		$entryPager->pageSize = self::PAGE_SIZE;
		$entryPager->pageIndex = 1;
		
		$entriesToReturn = array();
		do
		{
			$results = VidiunElasticSearchClientPlugin::get(VBatchBase::$vClient)->eSearch->searchEntry($entrySearchParams, $entryPager);
			
			foreach ($results->objects as $singleResult)
			{
				/* @var $singleResult VidiunESearchEntryResult */
				
				$entriesToReturn[] = $singleResult->object;
			}
			
			if (count($entriesToReturn) > self::LIMIT)
			{
				VidiunLog::info ('Upper limit for object count reached.');
				break;
			}
			
			$entryPager->pageIndex++;
		}
		while (count($results->objects) == self::PAGE_SIZE);
		
		$this->addContentToCsv ($entriesToReturn, $csvFile);
	}
	
	/**
	 * Generate the first csv row containing the fields
	 */
	protected function addHeaderRowToCsv($csvFile, $additionalFields)
	{
		$headerRow = 'EntryID, Name, Description, Tags, Categories, UserID, CreatedAt, UpdatedAt ';
		VCsvWrapper::sanitizedFputCsv($csvFile, explode(',', $headerRow));
		
		return $csvFile;
	}
	
	/**
	 * The function grabs all the fields values for each entry and adds them as a new row to the csv file
	 */
	protected function addContentToCsv($entriesArray, $csvFile)
	{
		if(!count($entriesArray))
			return;
		
		$entriesData = array();
		foreach ($entriesArray as $entry)
		{
			$entriesData[$entry->id] = $this->getCsvRowValues($entry);
		}
		
		foreach ($entriesData as $entryId => $values)
		{
			VCsvWrapper::sanitizedFputCsv($csvFile, $values);
		}
	}
	
	/**
	 * This function calculates the default values for CSV row representing a single entry and returns them as an array
	 *
	 * @param VidiunBaseEntry $entry
	 * @return array
	 */
	protected function getCsvRowValues (VidiunBaseEntry $entry)
	{
		$entryCategories = $this->retrieveEntryCategories ($entry->id);
		
		$values = array(
			$entry->id,
			$entry->name,
			$entry->description,
			$entry->tags,
			implode (',', $entryCategories),
			$entry->userId,
			$entry->createdAt,
			$entry->updatedAt,
		);
		
		return $values;
	}
	
	/**
	 * Function returns an array of every category the entry is published to.
	 *
	 * @param string $entryId
	 *
	 * @return array;
	 */
	protected function retrieveEntryCategories ($entryId)
	{
		$categoryEntryFilter = new VidiunCategoryEntryFilter();
		$categoryEntryFilter->entryIdEqual = $entryId;
		$categoryEntryFilter->statusEqual = VidiunCategoryEntryStatus::ACTIVE;
		
		$pager = new VidiunFilterPager();
		$pager->pageIndex = 1;
		$pager->pageSize = self::PAGE_SIZE;
		
		$categoryEntryResult = VBatchBase::$vClient->categoryEntry->listAction($categoryEntryFilter, $pager);
		
		$result = array();
		foreach ($categoryEntryResult->objects as $categoryEntry)
		{
			$result[] = $categoryEntry->categoryId;
		}
		
		return $result;
	}
}