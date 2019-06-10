<?php

/**
 * @package api
 * @subpackage objects
 */
class VidiunSiteCondition extends VidiunMatchCondition
{
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = ConditionType::SITE;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vSiteCondition();
			
		return parent::toObject($dbObject, $skip);
	}
}
