<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunBulkUploadAction extends VidiunDynamicEnum implements BulkUploadAction
{
	/**
	 * @return string
	 */
	public static function getEnumClass()
	{
		return 'BulkUploadAction';
	}
}
