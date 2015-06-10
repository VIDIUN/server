<?php
/**
 * @package api
 * @subpackage objects
 */
class KalturaInternalIpAddressCondition extends KalturaMatchCondition
{
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = ConditionType::INTERNAL_IP_ADDRESS;
	}
	
	/* (non-PHPdoc)
	 * @see KalturaObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new kInteranalIpAddressCondition();
			
		return parent::toObject($dbObject, $skip);
	}
}
