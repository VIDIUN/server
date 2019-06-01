<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunBatchJobType extends VidiunDynamicEnum implements BatchJobType
{
	/**
	 * @return string
	 */
	public static function getEnumClass()
	{
		return 'BatchJobType';
	}
}
