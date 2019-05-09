<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use VidiunRule instead
 */
class VidiunUserAgentRestriction extends VidiunBaseRestriction 
{
	/**
	 * User agent restriction type (Allow or deny)
	 * 
	 * @var VidiunUserAgentRestrictionType
	 */
	public $userAgentRestrictionType; 
	
	/**
	 * A comma seperated list of user agent regular expressions
	 * 
	 * @var string
	 */
	public $userAgentRegexList;
	
	private static $mapBetweenObjects = array
	(
		"userAgentRestrictionType",
		"userAgentRegexList",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseRestriction::toRule()
	 */
	public function toRule(VidiunRestrictionArray $restrictions)
	{
		return $this->toObject(new vAccessControlUserAgentRestriction());
	}
}