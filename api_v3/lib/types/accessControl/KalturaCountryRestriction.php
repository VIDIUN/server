<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use VidiunRule instead
 */
class VidiunCountryRestriction extends VidiunBaseRestriction 
{
	/**
	 * Country restriction type (Allow or deny)
	 * 
	 * @var VidiunCountryRestrictionType
	 */
	public $countryRestrictionType; 
	
	/**
	 * Comma separated list of country codes to allow to deny 
	 * 
	 * @var string
	 */
	public $countryList;
	
	private static $mapBetweenObjects = array
	(
		"countryRestrictionType",
		"countryList",
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
		return $this->toObject(new vAccessControlCountryRestriction());
	}
}