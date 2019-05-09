<?php
/**
 * Concat operation attributes
 * 
 * @package api
 * @subpackage objects
 */
class VidiunConcatAttributes extends VidiunOperationAttributes
{
	/**
	 * The resource to be concatenated
	 * @var VidiunDataCenterContentResource
	 */
	public $resource;

	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		throw new VidiunAPIException(VidiunErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($this));
	}
}