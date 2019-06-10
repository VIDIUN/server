<?php
/**
 * @package plugins.dropFolder
 * @subpackage Admin
 */
class Form_XmlFileHandlerConfig extends Form_BaseFileHandlerConfig
{
	/**
	 * {@inheritDoc}
	 * @see Form_BaseFileHandlerConfig::getFileHandlerType()
	 */
	protected function getFileHandlerType()
	{
		return Vidiun_Client_DropFolder_Enum_DropFolderFileHandlerType::XML;
	}

	public function populateFromObject($object, $add_underscore = true)
	{
		return;
	}
}
