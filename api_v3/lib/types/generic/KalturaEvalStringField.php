<?php
/**
 * Evaluates PHP statement, depends on the execution context
 * 
 * @package api
 * @subpackage objects
 */
class VidiunEvalStringField extends VidiunStringField
{
	/**
	 * PHP code
	 * @var string
	 * @requiresPermission all
	 */
	public $code;

	static private $map_between_objects = array
	(
		'code',
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vEvalStringField();
			
		return parent::toObject($dbObject, $skip);
	}
}