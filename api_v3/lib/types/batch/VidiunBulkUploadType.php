<?php

/**
 * 
 * Represents the bulk upload type
 * @author Roni
 * @package api
 * @subpackage enum
 *
 */
class VidiunBulkUploadType extends VidiunDynamicEnum implements BulkUploadType
{
	public static function getEnumClass()
	{
		return 'BulkUploadType';
	}
}