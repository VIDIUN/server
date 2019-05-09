<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUserAgentCondition extends VidiunRegexCondition
{
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = ConditionType::USER_AGENT;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vUserAgentCondition();
			
		return parent::toObject($dbObject, $skip);
	}
}
