<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunIpAddressCondition extends VidiunMatchCondition
{
	/**
	 * allow internal ips
	 * 
	 * @var bool
	 */
	public $acceptInternalIps;
	
	/**
	 * http header name for extracting the ip
	 * 
	 * @var string
	 */
	public $httpHeader;
	
	private static $mapBetweenObjects = array
	(
		'acceptInternalIps',
		'httpHeader',
	);
	
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = ConditionType::IP_ADDRESS;
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
			$dbObject = new vIpAddressCondition();
			
		return parent::toObject($dbObject, $skip);
	}
}
