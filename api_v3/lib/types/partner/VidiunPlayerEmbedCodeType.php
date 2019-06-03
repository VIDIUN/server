<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunPlayerEmbedCodeType extends VidiunObject
{
	/**
	 * @var string
	 */
	public $id;
	
	/**
	 * @var string
	 */
	public $label;
	
	/**
	 * @var bool
	 */
	public $entryOnly;
	
	/**
	 * @var string
	 */
	public $minVersion;
	
	private static $map_between_objects = array(
		'label', 
		'entryOnly', 
		'minVersion',
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
}