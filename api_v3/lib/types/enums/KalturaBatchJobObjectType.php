<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunBatchJobObjectType extends VidiunDynamicEnum implements BatchJobObjectType
{
	/**
	 * @return string
	 */
	public static function getEnumClass()
	{
		return 'BatchJobObjectType';
	}
}
