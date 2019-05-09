<?php
/**
 * @package plugins.tagSearch
 * @subpackage api.objects
 */
class VidiunTagListResponse extends VidiunListResponse
{
    /**
	 * @var VidiunTagArray
	 * @readonly
	 */
	public $objects;

	
	public function __construct()
	{
	    $this->objects = array();
	    $this->totalCount = count($this->objects);
	}
}