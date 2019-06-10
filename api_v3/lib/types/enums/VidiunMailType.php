<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunMailType extends VidiunDynamicEnum implements MailType
{
	public static function getEnumClass()
	{
		return 'MailType';
	}
}
