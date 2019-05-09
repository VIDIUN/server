<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunFileSyncObjectType extends VidiunDynamicEnum implements FileSyncObjectType 
{
	/**
	 * @return string
	 */
	public static function getEnumClass()
	{
		return 'FileSyncObjectType';
	}
}