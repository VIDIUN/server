<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunBulkUploadResultStatus extends VidiunDynamicEnum implements BulkUploadResultStatus
{
	public static function getEnumClass()
	{
		return 'BulkUploadResultStatus';
	}
}