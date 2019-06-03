<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunPublisherEnvironmentType extends VidiunDynamicEnum implements PublisherEnvironmentType
{
	/**
	 * @return string
	 */
	public static function getEnumClass()
	{
		return 'PublisherEnvironmentType';
	}
}