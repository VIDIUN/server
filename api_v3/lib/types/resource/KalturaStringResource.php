<?php
/**
 * Used to ingest string content.
 * 
 * @package api
 * @subpackage objects
 */
class VidiunStringResource extends VidiunContentResource
{
	/**
	 * Textual content
	 * @var string
	 */
	public $content;
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUsage($sourceObject, $propertiesToSkip)
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);
		
		$this->validatePropertyNotNull('content');
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if(!$object_to_fill)
			$object_to_fill = new vLocalFileResource();
		
		vFileBase::setFileContent($fname = tempnam(myContentStorage::getFSUploadsPath(), "KFR"), $this->content);
		$object_to_fill->setLocalFilePath($fname);
		$object_to_fill->setSourceType(entry::ENTRY_MEDIA_SOURCE_TEXT);
		
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}
