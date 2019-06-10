<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vAccessControlLimitThumbnailCaptureAction extends vRuleAction 
{
	public function __construct() 
	{
		parent::__construct(RuleActionType::LIMIT_THUMBNAIL_CAPTURE);
	}
}
