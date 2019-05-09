<?php
/**
 * Plugins may add bulk upload types
 * The bulk upload type should enable object loading of vBulkUploadJobData, VidiunBulkUploadJobData and VBulkUploadEngine
 * The plugin must expend BulkUploadType enum with the added new type
 * 
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunBulkUpload extends IVidiunBase, IVidiunEnumerator, IVidiunObjectLoader
{
	/**
	 * Returns the correct file extension for bulk upload type
	 * @param int $enumValue code API value
	 */
	public static function getFileExtension($enumValue);
	
	
	/**
	 * Returns the log file for bulk upload job
	 * @param BatchJob $batchJob bulk upload batchjob
	 */
	public static function writeBulkUploadLogFile($batchJob);
}