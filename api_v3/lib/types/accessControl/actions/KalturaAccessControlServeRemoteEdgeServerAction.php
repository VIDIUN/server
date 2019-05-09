<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAccessControlServeRemoteEdgeServerAction extends VidiunRuleAction
{
	/**
	 * Comma separated list of edge servers playBack should be done from
	 * 
	 * @var string
	 */
	public $edgeServerIds;

	/**
	 * @var VidiunNullableBoolean
	 */
	public $seamlessFallbackEnabled;

	
	private static $mapBetweenObjects = array
	(
		'edgeServerIds',
		'seamlessFallbackEnabled',
	);
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = RuleActionType::SERVE_FROM_REMOTE_SERVER;
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
			$dbObject = new vAccessControlServeRemoteEdgeServerAction();
			
		return parent::toObject($dbObject, $skip);
	}
}