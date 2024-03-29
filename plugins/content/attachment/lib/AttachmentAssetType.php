<?php
/**
 * @package plugins.attachment
 * @subpackage lib.enum
 */
class AttachmentAssetType implements IVidiunPluginEnum, assetType
{
	const ATTACHMENT = 'Attachment';
	
	public static function getAdditionalValues()
	{
		return array(
			'ATTACHMENT' => self::ATTACHMENT,
		);
	}
	
	/**
	 * @return array
	 */
	public static function getAdditionalDescriptions()
	{
		return array();
	}
}
