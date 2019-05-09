<?php
/**
 * After making sure the ticket is a valid admin ticket - the setrvice is allowed and no other validations should be done
 * 
 * @package api
 * @subpackage ps2
 */
class collectstatsAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "collectStats",
				"desc" => "collect statiscits about special events from the client" ,
				"in" => array (
					"mandatory" => array ( 
						"obj_type" => array ("type" => "string", "desc" => ""),
						"obj_id" => array ("type" => "string", "desc" => ""),
						"command" => array ("type" => "string", "desc" => ""),
						"value" => array ("type" => "string", "desc" => ""),
						"extra_info" => array ("type" => "string", "desc" => ""),
						),
					"optional" => array (
						"vshow_id" => array ("type" => "string", "desc" => "")
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
		return self::REQUIED_TICKET_REGULAR;
	}

	// ask to fetch the vuser from puser_vuser - so we can tel the difference between a
	public function needVuserFromPuser ( )
	{
		return self::VUSER_DATA_NO_VUSER;
	}

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$obj_type = $this->getPM ( "obj_type" );
		$obj_id = $this->getPM ( "obj_id" );
		$command = $this->getPM ( "command" );
		$value = $this->getP ( "value" );
		$extra_info = $this->getP ( "extra_info" );
		
		if ( $obj_type == "entry" )
		{
			$entry = entryPeer::retrieveByPK( $obj_id );
			if ( $command == "view" )
			{
				PartnerActivity::incrementActivity($partner_id, PartnerActivity::PARTNER_ACTIVITY_VDP, PartnerActivity::PARTNER_SUB_ACTIVITY_VDP_VIEWS);
				myStatisticsMgr::incEntryViews( $entry );
			}
			elseif ( $command == "play" )
			{
				PartnerActivity::incrementActivity($partner_id, PartnerActivity::PARTNER_ACTIVITY_VDP, PartnerActivity::PARTNER_SUB_ACTIVITY_VDP_PLAYS);
				myStatisticsMgr::incEntryPlays( $entry );
			}
		}
		elseif ( $obj_type == "vshow" )
		{
			$vshow = vshowPeer::retrieveByPK( $obj_id );
			if ( $command == "view" )
			{
				PartnerActivity::incrementActivity($partner_id, PartnerActivity::PARTNER_ACTIVITY_VDP, PartnerActivity::PARTNER_SUB_ACTIVITY_VDP_VIEWS);
				myStatisticsMgr::incVshowViews( $vshow );
			}
			elseif ( $command == "play" )
			{
				PartnerActivity::incrementActivity($partner_id, PartnerActivity::PARTNER_ACTIVITY_VDP, PartnerActivity::PARTNER_SUB_ACTIVITY_VDP_PLAYS);
				myStatisticsMgr::incVshowPlays( $vshow );
			}
		}	

		$this->addMsg( "collectedStats" , "$obj_type, $obj_id, $command, $value, $extra_info");
	}
}
?>