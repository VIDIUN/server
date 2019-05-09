<?php

/**
 * Subclass for representing a row from the 'moderation_flag' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class moderationFlag extends BasemoderationFlag implements IBaseObject
{
	public function getPuserId()
	{
		$vuser = $this->getvuserRelatedByVuserId();
		if ($vuser)
			return $vuser->getPuserId();
		else
			return null;
	}
	
	public function getFlaggedPuserId()
	{
		$flaggedVuser = $this->getvuserRelatedByFlaggedVuserId();
		if ($flaggedVuser)
			return $flaggedVuser->getPuserId();
		else
			return null;
	}
}
