<?php
/**
 * Used to ingest media file that is already accessible on the shared disc.
 * 
 * @package api
 * @subpackage objects
 */
class VidiunServerFileResource extends VidiunGenericDataCenterContentResource
{
	/**
	 * Full path to the local file 
	 * @var string
	 * @requiresPermission all
	 */
	public $localFilePath;
	
	/**
	 * Should keep original file (false = mv, true = cp)
	 * @var bool
	 */
	public $keepOriginalFile;
	
	private static $map_between_objects = array('localFilePath','keepOriginalFile');
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunDataCenterContentResource::validateForUsage()
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);
		
		$this->validatePropertyNotNull('localFilePath');
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if(!$object_to_fill)
			$object_to_fill = new vLocalFileResource();
		
		$keepOriginalFile = true;
		if(isset($this->keepOriginalFile) && $this->keepOriginalFile === false)
			$keepOriginalFile = false;
		
		$object_to_fill->setKeepOriginalFile($keepOriginalFile);
		$ret = parent::toObject($object_to_fill, $props_to_skip);
		/* @var $ret vLocalFileResource */
		
		if(!file_exists($ret->getLocalFilePath()))
			throw new VidiunAPIException(VidiunErrors::LOCAL_FILE_NOT_FOUND, $ret->getLocalFilePath());
		
		return $ret;
	}
}