<?php
/**
 * @package api
 * @subpackage ps2
 */
class cloneroughcutAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "cloneRoughcut",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"entry_id" => array ("type" => "string", "desc" => "")
						),
					"optional" => array (
						"detailed" => array ("type" => "boolean", "desc" => "")
						)
					),
				"out" => array (
					"entry" => array ("type" => "entry", "desc" => "")
					),
				"errors" => array (
					APIErrors::INVALID_ENTRY_ID,
					APIErrors::VSHOW_CLONE_FAILED ,
				)
			); 
	}
	
	protected function ticketType ()
	{
		return self::REQUIED_TICKET_ADMIN;
	}

	// check to see if already exists in the system = ask to fetch the puser & the vuser
	// don't ask for  VUSER_DATA_VUSER_DATA - because then we won't tell the difference between a missing vuser and a missing puser_vuser
	public function needVuserFromPuser ( )
	{
		return self::VUSER_DATA_VUSER_ID_ONLY;
	}

	protected function addUserOnDemand ( )
	{
		return self::CREATE_USER_FROM_PARTNER_SETTINGS;
	}
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$entry_id = $this->getPM ( "entry_id" );
		$detailed = $this->getP ( "detailed" , false );
		
		$entry = null;
		if ( $entry_id )
		{
			$entry = entryPeer::retrieveByPK( $entry_id );
		}
		
		if ( !$entry)
		{
			$this->addError ( APIErrors::INVALID_ENTRY_ID , $entry_id );
		}
		else
		{
			$vshow_id = $entry->getVshowId();
			$vshow = $entry->getVshow();
		
			if ( ! $vshow )
			{
				$this->addError ( APIErrors::INVALID_VSHOW_ID , $vshow_id );
			}
			else
			{
				$newVshow = myVshowUtils::shalowCloneById( $vshow_id , $puser_vuser->getVuserId() );
				
				if (!$newVshow)
				{
					$this->addError ( APIErrors::VSHOW_CLONE_FAILED , $vshow_id );
				}
				else
				{
					$newEntry = $newVshow->getShowEntry();
					
					$level = ( $detailed ? objectWrapperBase::DETAIL_LEVEL_DETAILED : objectWrapperBase::DETAIL_LEVEL_REGULAR );
					$wrapper = objectWrapperBase::getWrapperClass( $newEntry , $level );
					// TODO - remove this code when cache works properly when saving objects (in their save method)
					$wrapper->removeFromCache( "entry" , $newEntry->getId() );
					$this->addMsg ( "entry" , $wrapper ) ;
				}
			}
		}
	}
}
?>