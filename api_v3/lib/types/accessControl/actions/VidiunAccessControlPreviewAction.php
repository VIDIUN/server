<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAccessControlPreviewAction extends VidiunRuleAction
{
	/**
	 * @var int
	 */
	public $limit;
	
	private static $mapBetweenObjects = array
	(
		'limit',
	);

	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = RuleActionType::PREVIEW;
	}
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vAccessControlPreviewAction();
			
		return parent::toObject($dbObject, $skip);
	}
}