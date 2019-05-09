<?php
/**
 * @package plugins.businessProcessNotification
 * @subpackage api.enum
 */
class VidiunBusinessProcessServerStatus extends VidiunDynamicEnum implements BusinessProcessServerStatus
{
	public static function getEnumClass()
	{
		return 'BusinessProcessServerStatus';
	}
}