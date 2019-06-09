<?php
/**
 * @package plugins.attachment
 * @subpackage api.enum
 */
class VidiunAttachmentType extends VidiunDynamicEnum implements AttachmentType
{
	public static function getEnumClass()
	{
		return 'AttachmentType';
	}
}