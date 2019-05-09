<?php
/**
 * @package api
 * @subpackage ps2
 */
class getallentriesAction extends defPartnerservices2Action
{
	const LIST_TYPE_VSHOW = 1 ;
	const LIST_TYPE_VUSER = 2 ;
	const LIST_TYPE_ROUGHCUT = 4 ;
	const LIST_TYPE_EPISODE = 8 ;
	const LIST_TYPE_ALL = 15;

	public function describe()
	{
		return 
			array (
				"display_name" => "getAllEntries",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"entry_id" => array ("type" => "string", "desc" => ""),
						"vshow_id" => array ("type" => "string", "desc" => "")
						),
					"optional" => array (
						"list_type" => array ("type" => "integer", "desc" => ""), // TODO: describe enum
						"version" => array ("type" => "integer", "desc" => ""),
						"disable_roughcut_entry_data" => array ("type" => "boolean", "desc" => "indicaes the roughcut_entry_data is not required in the response"),
						)
					),
				"out" => array (
					"show" => array ("type" => "*entry", "desc" => ""),
					"roughcut_entry_data" => array ("type" => "array", "desc" => ""),
					"user" => array ("type" => "*entry", "desc" => "")
					),
				"errors" => array (
				)
			); 
	}
	
	protected function ticketType ()	{		return self::REQUIED_TICKET_REGULAR;	}

	protected function needVuserFromPuser ( )	
	{	
			return self::VUSER_DATA_NO_VUSER;
	}
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		if (!$partner_id)
			die;
		$entry_id = $this->getP ( "entry_id" );
		
		// if the entry_type was sent by the client - make sure it's of type  ENTRY_TYPE_SHOW.
		// this is to prevent this service as part of a bad multirequest
		$entry_type = $this->getP ( "entry_type" , null );
		if ( ! empty ( $entry_type ) && $entry_type != entryType::MIX )
		{
			$this->addDebug ( "entry" , "not of type " . entryType::MIX );
			return; 
		}
		
		$vshow_id =  $this->getP ( "vshow_id" );
		list ( $vshow , $entry , $error , $error_obj ) = myVshowUtils::getVshowAndEntry( $vshow_id  , $entry_id );

		if ( $error_obj )
		{
			$this->addError ( $error_obj );
			return ;
		}

		VidiunLog::log ( __METHOD__ . " vshow_id [$vshow_id] entry_id [$entry_id]" );


		$list_type = $this->getP ( "list_type" , self::LIST_TYPE_ROUGHCUT );
		$version = $this->getP ( "version" , null  );
		if ((int)$version == -1)
			$version = null; // will retrieve the latest
		$disable_roughcut_entry_data = $this->getP ( "disable_roughcut_entry_data" , false  );
		$disable_user_data = $this->getP ( "disable_user_data" , false  ); // will allow to optimize the cals and NOT join with the vuser table
		
		$merge_entry_lists = false;
		if ( $this->getPartner() )
		{
			$merge_entry_lists = $this->getPartner()->getMergeEntryLists();
		}
		
		$vshow_entry_list = array();
		$vuser_entry_list = array();

		$aggrigate_id_list = array();
$this->benchmarkStart( "list_type_vshow" );		
		if ( ($list_type & self::LIST_TYPE_VSHOW) && $vshow_id)
		{
			$c = new Criteria();
			$c->addAnd ( entryPeer::TYPE , entryType::MEDIA_CLIP );
//			$c->addAnd ( entryPeer::MEDIA_TYPE , entry::ENTRY_MEDIA_TYPE_SHOW , Criteria::NOT_EQUAL );
			$c->addAnd ( entryPeer::VSHOW_ID , $vshow_id );
			$this->addIgnoreIdList ($c , $aggrigate_id_list);
			
//			$this->addOffsetAndLimit ( $c ); // fetch as many as the vshow has
			if ( $disable_user_data )
				$vshow_entry_list = entryPeer::doSelect( $c );
			else
				$vshow_entry_list = entryPeer::doSelectJoinvuser( $c );
				
			$this->updateAggrigatedIdList( $aggrigate_id_list , $vshow_entry_list );
		}
		
$this->benchmarkEnd ( "list_type_vshow" );
$this->benchmarkStart( "list_type_vuser" );
		if ( $list_type & self::LIST_TYPE_VUSER )
		{
			// try to get puserVuser - PS2
			$puser_vuser = PuserVuserPeer::retrieveByPartnerAndUid ( $partner_id , null /*$subp_id*/,  $puser_id , false );
			// try to get vuser by partnerId & puserId - PS3 - backward compatibility
			$apiv3Vuser = vuserPeer::getVuserByPartnerAndUid($partner_id, $puser_id, true);
			
			if ( ($puser_vuser && $puser_vuser->getVuserId()) || $apiv3Vuser )
			{
				$c = new Criteria();
				$c->addAnd ( entryPeer::TYPE , entryType::MEDIA_CLIP );
//				$c->addAnd ( entryPeer::MEDIA_TYPE , entry::ENTRY_MEDIA_TYPE_SHOW , Criteria::NOT_EQUAL );
				$vuserIds = array();
				if($puser_vuser && $puser_vuser->getVuserId())
				{
					$vuserIds[] = $puser_vuser->getVuserId();
				}
				if($apiv3Vuser)
				{
					if(!$puser_vuser || ($puser_vuser->getVuserId() != $apiv3Vuser->getId()))
					{
						$vuserIds[] = $apiv3Vuser->getId();
					}
				}
/*
				if(count($vuserIds) > 1)
				{
					$c->addAnd ( entryPeer::VUSER_ID , $vuserIds, Criteria::IN );
				}
				else
				{
					$c->addAnd ( entryPeer::VUSER_ID , $vuserIds[0] );
				}
				if ( $merge_entry_lists )
				{
					// if will join lists - no need to fetch entries twice
					$this->addIgnoreIdList ($c , $aggrigate_id_list);
				}
				$this->addOffsetAndLimit ( $c ); // limit the number of the user's clips
				if ( $disable_user_data )
					$vuser_entry_list = entryPeer::doSelect( $c );
				else
					$vuser_entry_list = entryPeer::doSelectJoinvuser( $c );
*/
				$this->addOffsetAndLimit ( $c ); // limit the number of the user's clips
				if ( $merge_entry_lists )
				{
					// if will join lists - no need to fetch entries twice
					$this->addIgnoreIdList ($c , $aggrigate_id_list);
				}
				
				$vuser_entry_list = array(); 
				$vuserIds = array_unique($vuserIds);
				foreach($vuserIds as $vuserId)
				{
					$newC = clone $c;
					$newC->addAnd ( entryPeer::VUSER_ID , $vuserId );

					if ( $disable_user_data )
						$one_vuser_list = entryPeer::doSelect( $newC );
					else
						$one_vuser_list = entryPeer::doSelectJoinvuser( $newC );
					
					$vuser_entry_list = array_merge($vuser_entry_list, $one_vuser_list);
				}
					
				// Since we are using 2 potential vusers, we might not have the obvious vuser from $puser_vuser
				$strEntries = "";
				if($puser_vuser)
				{	
					$vuser = vuserPeer::retrieveByPk($puser_vuser->getVuserId());
					if ($vuser)
					{
						$strEntriesTemp = @unserialize($vuser->getPartnerData());
						if ($strEntriesTemp)
							$strEntries .= $strEntriesTemp;
					}
				}
				if ($apiv3Vuser)
				{
					$strEntriesTemp = @unserialize($apiv3Vuser->getPartnerData());
					if ($strEntriesTemp)
							$strEntries .= $strEntriesTemp;
				}
				
				if ($strEntries)
				{
					$entries = explode(',', $strEntries);
					$fixed_entry_list = array();
					foreach ( $entries as $entryId ) {
					  $fixed_entry_list[] = trim($entryId);
					}
					$c = new Criteria();
					$c->addAnd ( entryPeer::TYPE , entryType::MEDIA_CLIP );
					$c->addAnd ( entryPeer::ID , $fixed_entry_list, Criteria::IN );
					if ( $merge_entry_lists )
					{
						// if will join lists - no need to fetch entries twice
						$this->addIgnoreIdList ($c , $aggrigate_id_list);
					}
					if ( $disable_user_data )
						$extra_user_entries = entryPeer::doSelect( $c );
					else
						$extra_user_entries = entryPeer::doSelectJoinvuser( $c );
										
					if (count($extra_user_entries))
					{
						$vuser_entry_list = array_merge($extra_user_entries, $vuser_entry_list);
					}
				}
			}
			else
			{
				$vuser_entry_list = array();
			}	
			
			if ( $merge_entry_lists )
			{
				$vshow_entry_list = vArray::append  ( $vshow_entry_list , $vuser_entry_list );
				$vuser_entry_list = array();
			}
		}
$this->benchmarkEnd( "list_type_vuser" );
$this->benchmarkStart( "list_type_episode" );
		if ( $list_type & self::LIST_TYPE_EPISODE )
		{
			if ( $vshow && $vshow->getEpisodeId() )
			{
				// episode_id will point to the "parent" vshow
				// fetch the entries of the parent vshow
				$c = new Criteria();
				$c->addAnd ( entryPeer::TYPE , entryType::MEDIA_CLIP );
//				$c->addAnd ( entryPeer::MEDIA_TYPE , entry::ENTRY_MEDIA_TYPE_SHOW , Criteria::NOT_EQUAL );
				$c->addAnd ( entryPeer::VSHOW_ID , $vshow->getEpisodeId() );
				$this->addIgnoreIdList ($c , $aggrigate_id_list);
//				$this->addOffsetAndLimit ( $c ); // limit the number of the inherited entries from the episode
				if ( $disable_user_data )
					$parent_vshow_entries = entryPeer::doSelect( $c );
				else
					$parent_vshow_entries = entryPeer::doSelectJoinvuser( $c );
				
				if ( count ( $parent_vshow_entries) )
				{
					$vshow_entry_list = vArray::append  ( $vshow_entry_list , $parent_vshow_entries );
				}
			}
		}
$this->benchmarkEnd( "list_type_episode" );
		// fetch all entries that were used in the roughcut - those of other vusers
		// - appeared under vuser_entry_list when someone else logged in
$this->benchmarkStart( "list_type_roughcut" );
		$entry_data_from_roughcut_map = array(); // will hold an associative array where the id is the key
		if ( $list_type & self::LIST_TYPE_ROUGHCUT )
		{
			if ( $entry->getType() == entryType::MIX ) //&& $vshow->getHasRoughcut() )
			{
				$sync_key = $entry->getSyncKey ( entry::FILE_SYNC_ENTRY_SUB_TYPE_DATA , $version );
				$entry_data_from_roughcut = myFlvStreamer::getAllAssetsData ( $sync_key );

				$final_id_list = array();
				foreach ( $entry_data_from_roughcut as $data )
				{
					$id = $data["id"]; // first element is the id
					$entry_data_from_roughcut_map[] = $data;
					$found = false;
					foreach ( $vshow_entry_list as $entry )
					{
						// see we are not fetching duplicate entries
						if ( $entry->getId() == $id )
						{
							$found = true;
							break;
						}
					}
					if ( !$found )	$final_id_list[] = $id;
				}

				if ( count ( $final_id_list) > 0 ) // this is so we won't go to the DB for nothing - we'll receive an empty list anyway
				{
					// allow deleted entries when searching for entries on the roughcut 
					// don't forget to return the status at the end of the process
					entryPeer::allowDeletedInCriteriaFilter();
					
					$c = new Criteria();
					$c->addAnd ( entryPeer::ID , $final_id_list , Criteria::IN );
                	$c->addAnd ( entryPeer::TYPE , entryType::MEDIA_CLIP );					
					$this->addIgnoreIdList ($c , $aggrigate_id_list);
	//				$this->addOffsetAndLimit ( $c );
					
					if ( $disable_user_data )
						$extra_entries = entryPeer::doSelect( $c );
					else
						$extra_entries = entryPeer::doSelectJoinvuser( $c );
		
					// return the status to the criteriaFilter
					entryPeer::blockDeletedInCriteriaFilter();
						
					// merge the 2 lists into 1:
					$vshow_entry_list = vArray::append  ( $vshow_entry_list , $extra_entries );
				}
			}
		}
$this->benchmarkEnd( "list_type_roughcut" );
$this->benchmarkStart( "create_wrapper" );
		$entry_wrapper =  objectWrapperBase::getWrapperClass( $vshow_entry_list , objectWrapperBase::DETAIL_LEVEL_REGULAR , -3 ,0 ,array ( "contributorScreenName" ) );
		//$entry_wrapper->addFields ( array ( "vuser.screenName" ) );
		$this->addMsg ( "show" , $entry_wrapper );
		// if ! $disable_roughcut_entry_data - add the roughcut_entry_data
		if ( ! $disable_roughcut_entry_data )
			$this->addMsg ( "roughcut_entry_data" , $entry_data_from_roughcut_map );
		if ( count ( $vuser_entry_list ) > 0 ) 
		{
			$this->addMsg ( "user" ,  objectWrapperBase::getWrapperClass( $vuser_entry_list , objectWrapperBase::DETAIL_LEVEL_REGULAR ) );
		}
		else
		{
			$this->addMsg ( "user" ,  null );
		}
		
$this->benchmarkEnd( "create_wrapper" );		
	}
	
	private function addOffsetAndLimit ( Criteria $c )
	{
		$size = $this->getP ( "page_size" , 40 );
		if ( $size > 100 ) $size = 100;
		$page = $this->getP ( "page" , 1 );
				
		$offset = ($page-1)* $size;
		if ( $offset > 0 )	$c->setOffset( $offset );		
		
		$c->setLimit( $size);
	}
	
	private function addIgnoreIdList ( Criteria $c , $list )
	{
		if ( $list && count ( $list ) > 0 )
		{
			$c->addAnd ( entryPeer::ID , $list , Criteria::NOT_IN );
		}
	}
	
	private function updateAggrigatedIdList( &$aggrigate_id_list , $entry_list )
	{
		if ( !$entry_list || count ( $entry_list ) <= 0 ) return;
		foreach ( $entry_list as $entry )
		{
			$id = $entry->getId();
			if ( ! key_exists( $id , $aggrigate_id_list ) ) $aggrigate_id_list[]= $id;
		}
	}
}
?>
