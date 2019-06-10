<?php
/**
 * @package plugins.sip
 * @subpackage api.objects
 */
class VidiunSipResponse extends VidiunObject{

	/**
	 * @var string
	 */
	public $action;

	private static $mapBetweenObjects = array
	(
		'action'
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
}
