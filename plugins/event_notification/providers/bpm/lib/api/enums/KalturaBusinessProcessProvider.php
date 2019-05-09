<?php
/**
 * @package plugins.businessProcessNotification
 * @subpackage api.enum
 * @see BusinessProcessProvider
 */
class VidiunBusinessProcessProvider extends VidiunDynamicEnum implements BusinessProcessProvider
{
	public static function getEnumClass()
	{
		return 'BusinessProcessProvider';
	}
}