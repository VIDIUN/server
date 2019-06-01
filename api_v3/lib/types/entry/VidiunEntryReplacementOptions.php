<?php
/**
 * Advanced configuration for entry replacement process
 * @package api
 * @subpackage objects
 */
class VidiunEntryReplacementOptions extends VidiunObject
{
	/**
	 * If true manually created thumbnails will not be deleted on entry replacement
	 * @var int
	 */
	public $keepManualThumbnails;

	/**
	 * Array of plugin replacement options
	 * @var VidiunPluginReplacementOptionsArray
	 */
	public $pluginOptionItems;

	private static $mapBetweenObjects = array
	(
		'keepManualThumbnails',
		'pluginOptionItems',
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		if(!$object_to_fill)
			$object_to_fill = new vEntryReplacementOptions();
		
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}
