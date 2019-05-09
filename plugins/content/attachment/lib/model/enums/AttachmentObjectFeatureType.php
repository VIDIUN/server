<?php
/**
 * @package plugins.attachment
 * @subpackage model.enum
 */
class AttachmentObjectFeatureType implements IVidiunPluginEnum, ObjectFeatureType
{
	const ATTACHMENT = 'Attachment';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues() 
	{
		return array
		(
			'ATTACHMENT' => self::ATTACHMENT,
		);
		
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() {
		return array();
		
	}
}