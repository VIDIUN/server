<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunStorageAddAction extends VidiunRuleAction
{
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = RuleActionType::ADD_TO_STORAGE;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vRuleAction(RuleActionType::ADD_TO_STORAGE);
			
		return parent::toObject($dbObject, $skip);
	}
}