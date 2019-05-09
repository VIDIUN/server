<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunBulkUploadObjectType extends VidiunDynamicEnum implements BulkUploadObjectType
{
	/**
	 * @return string
	 */
	public static function getEnumClass()
	{
		return 'BulkUploadObjectType';
	}
}