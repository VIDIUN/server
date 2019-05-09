<?php

/**
 * Represents the Bulk service input for filter bulk upload
 * @package plugins.bulkUploadFilter
 * @subpackage api.objects
 */
class VidiunBulkServiceFilterDataBase extends VidiunBulkServiceData
{
	/**
	 * Filter for extracting the objects list to upload
	 * @var VidiunFilter
	 */
	public $filter;


	public function getType ()
	{
		return vPluginableEnumsManager::apiToCore("BulkUploadType", BulkUploadFilterPlugin::getApiValue(BulkUploadFilterType::FILTER));
	}

	public function toBulkUploadJobData(VidiunBulkUploadJobData $jobData)
	{
		$jobData->filter = $this->filter;
	}
}