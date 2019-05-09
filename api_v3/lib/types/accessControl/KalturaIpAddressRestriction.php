<?php
/**
 * @package api
 * @subpackage objects
 * @deprecated use VidiunRule instead
 */
class VidiunIpAddressRestriction extends VidiunBaseRestriction 
{
	/**
	 * Ip address restriction type (Allow or deny)
	 * 
	 * @var VidiunIpAddressRestrictionType
	 */
	public $ipAddressRestrictionType; 
	
	/**
	 * Comma separated list of ip address to allow to deny 
	 * 
	 * @var string
	 */
	public $ipAddressList;
	
	private static $mapBetweenObjects = array
	(
		"ipAddressRestrictionType",
		"ipAddressList",
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
		return $this->toObject(new vAccessControlIpAddressRestriction());
	}
}