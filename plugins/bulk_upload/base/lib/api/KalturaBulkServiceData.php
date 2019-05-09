<?php
/**
 * This class represents object-specific data passed to the 
 * bulk upload job.
 * @abstract
 * @package plugins.bulkUpload
 * @subpackage api.objects
 *
 */
abstract class VidiunBulkServiceData extends VidiunObject
{
	abstract public function getType ();
	abstract public function toBulkUploadJobData(VidiunBulkUploadJobData $jobData);
}