<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAccessControlBlockAction extends VidiunRuleAction
{
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = RuleActionType::BLOCK;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vRuleAction(RuleActionType::BLOCK);
			
		return parent::toObject($dbObject, $skip);
	}
}