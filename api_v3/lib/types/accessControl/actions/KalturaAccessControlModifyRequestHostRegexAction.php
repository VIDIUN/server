<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAccessControlModifyRequestHostRegexAction extends VidiunRuleAction
{
	/**
	 * Request host regex pattern
	 * 
	 * @var string
	 */
	public $pattern;
	
	/**
	 * Request host regex replacment
	 *
	 * @var string
	 */
	public $replacement;
	
	/**
	 * serverNodeId to generate replacment host from
	 *
	 * @var int
	 */
	public $replacmenServerNodeId;
	
	private static $mapBetweenObjects = array
	(
		'pattern',
		'replacement',
		'replacmenServerNodeId',
	);
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = RuleActionType::REQUEST_HOST_REGEX;
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
			$dbObject = new vAccessControlModifyRequestHostRegexAction();
			
		return parent::toObject($dbObject, $skip);
	}
}