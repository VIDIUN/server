<?php
/**
 * @package plugins.kontiki
 *  @subpackage model.enum
 */
class KontikiStorageProfileProtocol implements IVidiunPluginEnum, StorageProfileProtocol
{
	const KONTIKI = 'KONTIKI';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues() {
		return array('KONTIKI' => self::KONTIKI);
		
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() {
		return array();
		
	}

	
}