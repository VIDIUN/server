<?php
/**
 * @package plugins.confMaps
 * @subpackage api.objects
 */
class VidiunConfMapsArray extends VidiunTypedArray
{
	public function __construct()
	{
		parent::__construct("VidiunConfMaps");
	}
	public function insert(VidiunConfMaps $map)
	{
		$this->array[] = $map;
	}
}