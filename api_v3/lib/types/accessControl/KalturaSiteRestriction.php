<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use VidiunRule instead
 */
class VidiunSiteRestriction extends VidiunBaseRestriction 
{
	/**
	 * The site restriction type (allow or deny)
	 * 
	 * @var VidiunSiteRestrictionType
	 */
	public $siteRestrictionType;
	
	/**
	 * Comma separated list of sites (domains) to allow or deny
	 * 
	 * @var string
	 */
	public $siteList;
	
	private static $mapBetweenObjects = array
	(
		"siteRestrictionType",
		"siteList",
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
		return $this->toObject(new vAccessControlSiteRestriction());
	}
}