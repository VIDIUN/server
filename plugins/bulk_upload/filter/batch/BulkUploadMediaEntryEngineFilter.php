<?php

class BulkUploadMediaEntryEngineFilter extends BulkUploadEngineFilter
{
	const OBJECT_TYPE_TITLE = 'media entry';
	
	const ENTRY_TAG_XPATH = '/mrss/channel/item/entryId';
	
	const ACTION_TAG_XPATH = '/mrss/channel/item/action';
	
	protected function listObjects(VidiunFilter $filter, VidiunFilterPager $pager = null)
	{
		$filter->orderBy = "+createdAt";
		if ($filter instanceof VidiunBaseEntryFilter)
		{
			return VBatchBase::$vClient->baseEntry->listAction($filter, $pager);
		}
		else
		{
			throw new VidiunBatchException('Unsupported filter: {' . get_class($filter) . '}', VidiunBatchJobAppErrors::BULK_VALIDATION_FAILED);
		}
	}
	
	protected function createObjectFromResultAndJobData(VidiunBulkUploadResult $bulkUploadResult)
	{
		$entryId = $bulkUploadResult->jobObjectId;
		
		$doc = new VDOMDocument();
		$doc->load($this->data->filePath);
		
		$xpath = new DOMXPath($doc);
		
		$items = $xpath->query(self::ENTRY_TAG_XPATH);
		if (!$items->length)
		{
			throw new VidiunBatchException ('No {entryId} tag found in template bulk upload XML provided!');
		}
		
		foreach ($items as $item)
		{
			/* @var $item DOMNode */
			$item->nodeValue = $entryId;
		}
		
		$tmpFilePath = vFile::createTempFile($doc->saveXML());
		
		$bulkUploadJobData = new VidiunBulkUploadXmlJobData();
		$bulkUploadJobData->fileName = $this->job->id . '_' . $entryId . '.xml';
		
		VBatchBase::$vClient->media->bulkUploadAdd($tmpFilePath, $bulkUploadJobData);
	}
	
	protected function deleteObjectFromResult(VidiunBulkUploadResult $bulkUploadResult)
	{
		// TODO: Implement deleteObjectFromResult() method.
	}
	
	protected function fillUploadResultInstance($object)
	{
		$bulkUploadResult = new VidiunBulkUploadResultJob();
		$bulkUploadResult->bulkUploadJobId = $this->job->id;
		$bulkUploadResult->jobObjectId = $object->id;
		
		$doc = new VDOMDocument();
		$doc->load($this->data->filePath);
		
		$xpath = new DOMXPath($doc);
		
		$actions = $xpath->query(self::ACTION_TAG_XPATH);
		if (!$actions->length)
		{
			throw new VidiunBatchException ('No {action} tag found in template bulk upload XML provided!');
		}
		
		foreach ($actions as $action)
		{
			/* @var $action DOMNode */
			if (strval($action->nodeValue) == 'add')
			{
				throw new VidiunBatchException ('{action} tag value can only be set to values [update] and [delete]');
			}
		}
		
		return $bulkUploadResult;
	}
	
	protected function getBulkUploadResultObjectType()
	{
		return VidiunBulkUploadObjectType::JOB;
	}
	
	/**
	 *
	 * Get object type title for messaging purposes
	 */
	public function getObjectTypeTitle()
	{
		return self::OBJECT_TYPE_TITLE;
	}
}