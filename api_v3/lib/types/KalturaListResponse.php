<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunListResponse extends VidiunObject
{
	/**
	 * @var int
	 * @readonly
	 */
	public $totalCount;

	/* (non-PHPdoc)
	 * @see VidiunObject::loadRelatedObjects($responseProfile)
	 */
	public function loadRelatedObjects(VidiunDetachedResponseProfile $responseProfile)
	{
		if($this->objects)
		{
			$this->objects->loadRelatedObjects($responseProfile);
		}
	}
}