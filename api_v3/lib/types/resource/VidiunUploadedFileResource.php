<?php
/**
 * sed to ingest media that uploaded as posted file in this http request, the file data represents the $_FILE
 * 
 * @package api
 * @subpackage objects
 */
class VidiunUploadedFileResource extends VidiunGenericDataCenterContentResource
{
	/**
	 * Represents the $_FILE 
	 * @var file
	 */
	public $fileData;
	
	/* (non-PHPdoc)
	 * @see VidiunDataCenterContentResource::validateForUsage()
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);
		
		$this->validatePropertyNotNull('fileData');
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if(!$object_to_fill)
			$object_to_fill = new vLocalFileResource();
		
		$ext = pathinfo($this->fileData['name'], PATHINFO_EXTENSION);
		
		$uploadPath = $this->fileData['tmp_name'];
		$tempPath = myContentStorage::getFSUploadsPath() . '/' . uniqid(time()) . '.' . $ext;
		$moved = vFile::moveFile($uploadPath, $tempPath, true);
		if(!$moved)
			throw new VidiunAPIException(VidiunErrors::UPLOAD_ERROR);
		
		$object_to_fill->setLocalFilePath($tempPath);
		return $object_to_fill;
	}
}