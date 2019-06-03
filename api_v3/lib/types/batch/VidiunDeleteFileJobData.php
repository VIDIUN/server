<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunDeleteFileJobData extends VidiunJobData
{
	/**
	 * @var string
	 */
	public $localFileSyncPath;
	
	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject($source_object)
	 */
	public function doFromObject($sourceObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$this->localFileSyncPath = $sourceObject->getLocalFileSyncPath();
		parent::doFromObject($sourceObject, $responseProfile);
	}
	
}