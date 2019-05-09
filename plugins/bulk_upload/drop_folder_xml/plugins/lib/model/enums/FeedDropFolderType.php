<?php
/**
 * @package plugins.FeedDropFolder
 * @subpackage model.enum
 */
class FeedDropFolderType implements IVidiunPluginEnum, DropFolderType
{
	const FEED = 'FEED';
	
	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues() {
		return array('FEED' => self::FEED);
		
	}

	/* (non-PHPdoc)
	 * @see IVidiunPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() {
		return array();
		
	}
}