<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAccessControlLimitThumbnailCaptureAction extends VidiunRuleAction
{
	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = RuleActionType::LIMIT_THUMBNAIL_CAPTURE;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vAccessControlLimitThumbnailCaptureAction();
			
		return parent::toObject($dbObject, $skip);
	}
}