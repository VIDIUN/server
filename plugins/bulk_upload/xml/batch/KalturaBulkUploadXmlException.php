<?php
/**
 * @package plugins.bulkUploadXml
 * @subpackage Scheduler.BulkUpload
 */
class VidiunBulkUploadXmlException extends VidiunException
{
	public function __construct($message, $code, $arguments = null)
	{
		parent::__construct($message, $code, $arguments);
	}
}