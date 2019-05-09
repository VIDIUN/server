<?php
/**
 * @package plugins.webexDropFolder
 *  @subpackage model.enum
 */
class WebexDropFolderType implements IVidiunPluginEnum, DropFolderType
{
	const WEBEX = 'WEBEX';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues() {
		return array('WEBEX' => self::WEBEX);
		
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() {
		return array();
		
	}
}
