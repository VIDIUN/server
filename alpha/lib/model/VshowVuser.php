<?php
/**
 * Subclass for representing a row from the 'vshow_vuser' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class VshowVuser extends BaseVshowVuser
{
	// different type of subscriptions
	const VSHOW_SUBSCRIPTION_NORMAL = 1;
	
	// differnt types of viewers
	const VSHOWVUSER_VIEWER_USER = 0;
	const VSHOWVUSER_VIEWER_SUBSCRIBER = 1;
	const VSHOWVUSER_VIEWER_PRODUCER = 2;
	
	public function save(PropelPDO $con = null)
	{
		if ( $this->isNew() )
		{
			myStatisticsMgr::addSubscriber( $this );
		}
		
		parent::save( $con );
	}			
}
