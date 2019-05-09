<?php
/**
 * @package api
 * @subpackage filters
 */
class VidiunEntryIndexAdvancedFilter extends VidiunIndexAdvancedFilter
{	
	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(!$object_to_fill)
			$object_to_fill = new vEntryIndexAdvancedFilter();
			
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}
