<?php

/**
 * Represents the Bulk service input for filter bulk upload
 * @package plugins.bulkUploadFilter
 * @subpackage api.objects
 */
class VidiunBulkServiceFilterData extends VidiunBulkServiceFilterDataBase
{
	/**
	 * Template object for new object creation
	 * @var VidiunObject
	 */
	public $templateObject;

	
	public function toBulkUploadJobData(VidiunBulkUploadJobData $jobData)
	{
		$jobData->filter = $this->filter;
		$jobData->templateObject = $this->templateObject;
	}
}