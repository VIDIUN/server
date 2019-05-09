<?php
/**
 * @package plugins.dropFolderXmlBulkUpload
 * @subpackage lib
 */
class DropFolderXmlFileHandlerType implements IVidiunPluginEnum, DropFolderFileHandlerType
{
	const XML = 'XML';
	
	/**
	 * 
	 * Returns the dynamic enum additional values
	 */
	public static function getAdditionalValues()
	{
		return array(
			'XML' => self::XML,
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
