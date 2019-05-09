<?php
/**
 * This engine supports deletion of user entries based on the input filter
 *
 * @package plugins.bulkUploadFilter
 * @subpackage batch
 */
class BulkUploadUserEntryEngineFilter extends BulkUploadEngineFilter
{
	const OBJECT_TYPE_TITLE = 'user entry';

	/* get a list of objects according to the input filter
	 *
	 * @see BulkUploadEngineFilter::listObjects()
	 */
	protected function listObjects(VidiunFilter $filter, VidiunFilterPager $pager = null)
	{
		if($filter instanceof VidiunUserEntryFilter)
		{
			return VBatchBase::$vClient->userEntry->listAction($filter, $pager);
		}

		else
		{
			throw new VidiunBatchException("Unsupported filter: {get_class($filter)}", VidiunBatchJobAppErrors::BULK_VALIDATION_FAILED);
		}
	}

	protected function createObjectFromResultAndJobData (VidiunBulkUploadResult $bulkUploadResult)
	{
		//in bulk delete, there is no need to create objects from bulk upload result.
		return;
	}

	protected function deleteObjectFromResult (VidiunBulkUploadResult $bulkUploadResult)
	{
		return VBatchBase::$vClient->userEntry->delete($bulkUploadResult->userEntryId);
	}

	/**
	 * create specific instance of BulkUploadResult and set it's properties
	 * @param $object - Result is being created from VidiunUserEntry
	 *
	 * @see BulkUploadEngineFilter::fillUploadResultInstance()
	 */
	protected function fillUploadResultInstance ($object)
	{
		$bulkUploadResult = new VidiunBulkUploadResultUserEntry();
		if($object instanceof VidiunUserEntry)
		{
			//get user entry object based on the entry details
			$filter = new VidiunUserEntryFilter();
			$filter->idEqual = $object->id;
			$filter->userIdEqual = $object->userId;
			$filter->partnerId = $object->partnerId;
			$list = $this->listObjects($filter);
			if(count($list->objects))
			{
				$userEntry = reset($list->objects);
			}
		}
		if($userEntry)
		{
			$bulkUploadResult->objectId = $userEntry->id;
			$bulkUploadResult->objectStatus = $userEntry->status;
			$bulkUploadResult->userEntryId = $userEntry->id;
			$bulkUploadResult->action = VidiunBulkUploadAction::DELETE;
		}
		return $bulkUploadResult;
	}

	protected function getBulkUploadResultObjectType()
	{
		return VidiunBulkUploadObjectType::USER_ENTRY;
	}

	public function getObjectTypeTitle()
	{
		return self::OBJECT_TYPE_TITLE;
	}

}