<?php
/**
 * After making sure the ticket is a valid admin ticket - the setrvice is allowed and no other validations should be done
 * 
 * @package api
 * @subpackage ps2
 */
class rankvshowAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 	
			array (
				"display_name" => "rankVShow",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"vshow_id" => array ("type" => "string", "desc" => ""),
						"rank" => array ("type" => "integer", "desc" => "")
						),
					"optional" => array ()
					),
				"out" => array (
					"rank" => array ("type" => "array", "desc" => "")
					),
				"errors" => array (
					APIErrors::INVALID_RANK ,
					APIErrors::INVALID_VSHOW_ID , 
					APIErrors::USER_ALREADY_RANKED_VSHOW , 
					
				)
			); 
	}
	
	protected function ticketType()	{		return self::REQUIED_TICKET_REGULAR;	}
	// ask to fetch the vuser from puser_vuser - so we can tel the difference between a 
	public function needVuserFromPuser ( )	{		return self::VUSER_DATA_VUSER_ID_ONLY; 	}
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$vshow_id = $this->getPM ( "vshow_id" );
		$rank = $this->getPM ( "rank" );
		
		$vshow = vshowPeer::retrieveByPK( $vshow_id );
		
		if ( ! $vshow )
		{
			$this->addError( APIErrors::INVALID_VSHOW_ID , $vshow_id  );
			return;		
		}
		
		if ( $rank > entry::MAX_NORMALIZED_RANK || $rank < 0 || ! is_numeric( $rank ))
		{
			$this->addError( APIErrors::INVALID_RANK , $rank );
			return;					
		}

		$vuser_id = $puser_vuser->getVuserId();
		$entry_id = $vshow->getShowEntryId();
		
		$partner = PartnerPeer::retrieveByPK($partner_id);

		if (!$partner->getAllowAnonymousRanking()) 
		{
			// prevent duplicate votes
			$c = new Criteria ();
			$c->add ( vvotePeer::VUSER_ID , $vuser_id);
			$c->add ( vvotePeer::ENTRY_ID , $entry_id);
			$c->add ( vvotePeer::VSHOW_ID , $vshow_id);
			
			$vvote = vvotePeer::doSelectOne( $c );
			if ( $vvote != NULL )
			{
				$this->addError( APIErrors::USER_ALREADY_RANKED_VSHOW , $puser_id  , $vshow_id );
				return;						
			}
		}
		
		$vvote = new vvote();
		$vvote->setVshowId($vshow_id);
		$vvote->setEntryId($entry_id);
		$vvote->setVuserId($vuser_id);
		$vvote->setRank($rank);
		$vvote->save();

		$statistics_results = $vvote->getStatisticsResults();
		$updated_vshow = @$statistics_results["vshow"];
		
		if ( $updated_vshow )
		{
			myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_VSHOW_RANK , $updated_vshow );
			
			$data = array ( "vshow_id" => $vshow_id , 
				"uid" => $puser_id ,
				"rank" => $updated_vshow->getRank() ,
				"votes" => $updated_vshow->getVotes() );
				
			//$this->addMsg ( "vshow" , objectWrapperBase::getWrapperClass( $updated_vshow , objectWrapperBase::DETAIL_LEVEL_DETAILED) );
			$this->addMsg ( "rank" , $data ); 
		}

	}
}
?>