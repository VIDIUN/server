<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use VidiunRule instead
 */
class VidiunLimitFlavorsRestriction extends VidiunBaseRestriction 
{
	/**
	 * Limit flavors restriction type (Allow or deny)
	 * 
	 * @var VidiunLimitFlavorsRestrictionType
	 */
	public $limitFlavorsRestrictionType; 
	
	/**
	 * Comma separated list of flavor params ids to allow to deny 
	 * 
	 * @var string
	 */
	public $flavorParamsIds;
	
	private static $mapBetweenObjects = array
	(
		"limitFlavorsRestrictionType",
		"flavorParamsIds",
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
		return $this->toObject(new vAccessControlLimitFlavorsRestriction());
	}
}