<?php
/**
 * After making sure the ticket is a valid admin ticket - the setrvice is allowed and no other validations should be done
 * 
 * @package api
 * @subpackage ps2
 */
class deletevshowAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "deleteVShow",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"vshow_id" => array ("type" => "string", "desc" => "")
						),
					"optional" => array (
						)
					),
				"out" => array (
					"deleted_vshow" => array ("type" => "vshow", "desc" => "")
					),
				"errors" => array (
					APIErrors::INVALID_VSHOW_ID ,
				)
			); 
	}
	
	protected function ticketType()			{		return self::REQUIED_TICKET_ADMIN;	}
	// ask to fetch the vuser from puser_vuser - so we can tel the difference between a 
	public function needVuserFromPuser ( )	{		return self::VUSER_DATA_VUSER_ID_ONLY;	}
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$vshow_id_to_delete = $this->getPM ( "vshow_id" );
		
		$vshow_to_delete = vshowPeer::retrieveByPK( $vshow_id_to_delete );
		
		if ( ! $vshow_to_delete )
		{
			$this->addError( APIErrors::INVALID_VSHOW_ID , $vshow_id_to_delete );
			return;		
		}

		$vshow_to_delete->delete();

		myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_VSHOW_DELETE , $vshow_to_delete );
		
		$this->addMsg ( "deleted_vshow" , objectWrapperBase::getWrapperClass( $vshow_to_delete , objectWrapperBase::DETAIL_LEVEL_REGULAR) );
	}
}
?>