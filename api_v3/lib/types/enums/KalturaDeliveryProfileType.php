<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunDeliveryProfileType extends VidiunDynamicEnum implements DeliveryProfileType
{
	/**
	 * @return string
	 */
	public static function getEnumClass()
	{
		return 'DeliveryProfileType';
	}
}
