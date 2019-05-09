<?php
/**
 * After making sure the ticket is a valid admin ticket - the setrvice is allowed and no other validations should be done
 * 
 * @package api
 * @subpackage ps2
 */
class deleteentryAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "deleteEntry",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"entry_id" => array ("type" => "string", "desc" => "")
						),
					"optional" => array (
						)
					),
				"out" => array (
					"deleted_entry" => array ("type" => "entry", "desc" => "")
					),
				"errors" => array (
					 APIErrors::INVALID_ENTRY_ID ,
					 APIErrors::CANNOT_DELETE_ENTRY ,
				)
			); 
	}
	
	protected function ticketType()
	{
		return self::REQUIED_TICKET_ADMIN;
	}

	// ask to fetch the vuser from puser_vuser - so we can tel the difference between a
	public function needVuserFromPuser ( )
	{
		return self::VUSER_DATA_VUSER_ID_ONLY;
	}

	protected function getObjectPrefix () { return "entry"; }

	protected function getCriteria (  ) { return null; }

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$prefix = $this->getObjectPrefix();
		$entry_id_to_delete = $this->getPM ( "{$prefix}_id" );

		$vshow_id_for_entry_id_to_delete = $this->getP ( "vshow_id" );
		$c = $this->getCriteria(); 
		if ( $c == null )
		{
			$entry_to_delete = entryPeer::retrieveByPK( $entry_id_to_delete );
		}
		else
		{
			$entry_to_delete = entryPeer::doSelectOne( $c );
		}
				
		if ( ! $entry_to_delete )
		{
			$this->addError( APIErrors::INVALID_ENTRY_ID , $prefix , $entry_id_to_delete );
			return;
		}

		if ( $vshow_id_for_entry_id_to_delete != null )
		{
			// match the vshow_id
			if (  $vshow_id_for_entry_id_to_delete != $entry_to_delete->getVshowId() )
			{
				$this->addError( APIErrors::CANNOT_DELETE_ENTRY , $entry_id_to_delete , $vshow_id_for_entry_id_to_delete  );
				return;
			}
		}

		myEntryUtils::deleteEntry( $entry_to_delete ); 
		
		/*
			All move into myEntryUtils::deleteEntry
		
			$entry_to_delete->setStatus ( entryStatus::DELETED );
			
			// make sure the moderation_status is set to moderation::MODERATION_STATUS_DELETE
			$entry_to_delete->setModerationStatus ( moderation::MODERATION_STATUS_DELETE ); 
			$entry_to_delete->setModifiedAt( time() ) ;
			$entry_to_delete->save();
			
			myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_ENTRY_DELETE , $entry_to_delete );
		*/
		
		$this->addMsg ( "deleted_" . $prefix  , objectWrapperBase::getWrapperClass( $entry_to_delete , objectWrapperBase::DETAIL_LEVEL_REGULAR ) );

	}
}
?>