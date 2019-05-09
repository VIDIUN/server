<?php
/**
 * @package api
 * @subpackage ps2
 */
class updatevshowAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "updateVShow",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"vshow_id"				=> array ("type" => "string", "desc" => ""),
						"vshow" 				=> array ("type" => "vshow", "desc" => ""),
						),
					"optional" => array (
						"detailed" 				=> array ("type" => "boolean", "desc" => ""),
						"allow_duplicate_names" => array ("type" => "boolean", "desc" => "")
						)
					),
				"out" => array (
					"vshow" => array ("type" => "vshow", "desc" => "")
					),
				"errors" => array (
					APIErrors::INVALID_USER_ID , 
					APIErrors::INVALID_VSHOW_ID ,
					APIErrors::DUPLICATE_VSHOW_BY_NAME ,
					APIErrors::ERROR_VSHOW_ROLLBACK
				)
			); 
	}
	
	// ask to fetch the vuser from puser_vuser
	public function needVuserFromPuser ( )	{		return self::VUSER_DATA_VUSER_ID_ONLY;	}
	public function requiredPrivileges () { return "edit:<vshow_id>" ; }

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		if ( ! $puser_vuser )
		{
			$this->addError ( APIErrors::INVALID_USER_ID ,  $puser_id );
			return;
		}

		// get the new properties for the vshow from the request
		$vshow_update_data = new vshow();

		$start_obj_creation = microtime( true );
		$vshow = new vshow();
		$obj_wrapper = objectWrapperBase::getWrapperClass( $vshow  , 0 );
//		$this->addDebug ( "timer_getWrapperClass1" , ( microtime( true ) - $start_obj_creation ) );

		$timer = microtime( true );
		$fields_modified = baseObjectUtils::fillObjectFromMap ( $this->getInputParams() ,
			$vshow ,
			"vshow_" ,
			$obj_wrapper->getUpdateableFields() );

//		$this->addDebug ( "timer_fillObjectFromMap" , ( microtime( true ) - $timer ) );

		$vshow->setName( trim ( $vshow->getName() ) );

		$vshow_id = $this->getPM ( "vshow_id");
		$detailed = $this->getP ( "detailed" , false );
		$allow_duplicate_names = $this->getP ( "allow_duplicate_names" , true , true );
		if ( $allow_duplicate_names === "false" || $allow_duplicate_names === 0 ) $allow_duplicate_names = false;

		if ( count ( $fields_modified ) > 0 )
		{
			$timer = microtime( true );
			$vshow_from_db = vshowPeer::retrieveByPK( $vshow_id );
			if ( ! $vshow_from_db )
			{
				// vshow with this id does not exists in the DB
				$this->addError ( APIErrors::INVALID_VSHOW_ID ,  $vshow_id );

				return;
			}

			if ( ! $this->isOwnedBy ( $vshow_from_db , $puser_vuser->getVuserId() ) )
				$this->verifyPrivileges ( "edit" , $vshow_id ); // user was granted explicit permissions when initiatd the vs

							
			if ( myPartnerUtils::shouldForceUniqueVshow( $partner_id , $allow_duplicate_names ) )
			{
				$vshow_with_name_from_db = vshowPeer::getFirstVshowByName( $vshow->getName() );
				if ( $vshow_with_name_from_db && $vshow_with_name_from_db->getId() != $vshow_id )
				{
					$this->addError( APIErrors::DUPLICATE_VSHOW_BY_NAME ,   $vshow->getName() );
					$level = ( $detailed ? objectWrapperBase::DETAIL_LEVEL_DETAILED : objectWrapperBase::DETAIL_LEVEL_REGULAR );
					if( myPartnerUtils::returnDuplicateVshow( $partner_id ))
					{
						$this->addMsg ( "vshow" , objectWrapperBase::getWrapperClass( $vshow_from_db , $level  ) );
					}					
					return;
				}
			}

			$this->addMsg ( "old_vshow" , objectWrapperBase::getWrapperClass( $vshow_from_db->copy() , objectWrapperBase::DETAIL_LEVEL_REGULAR ) );

//			$this->addDebug ( "timer_db_retrieve" , ( microtime( true ) - $timer ) );

			$timer = microtime( true );
			// copy relevant fields from $vshow -> $vshow_update_data
			baseObjectUtils::fillObjectFromObject( $obj_wrapper->getUpdateableFields() ,
				$vshow ,
				$vshow_from_db ,
				baseObjectUtils::CLONE_POLICY_PREFER_NEW , null , BasePeer::TYPE_PHPNAME );

//			$this->addDebug ( "timer_fillObjectFromObject" , ( microtime( true ) - $timer ) );

			$timer = microtime( true );

			// TODO - move to some generic place myVshowUtils / vshow.php
			// TODO - this should be called only for the first time or whenever the user wants to force overriding the sample_text
			$force_sample_text = $this->getP ( "force_sample_text" , false );
			$force_sample_text = false;

			$vuser_id = $puser_vuser->getVuserId();
/*
			$sample_text = "This is a collaborative video for &#xD;'" . $vshow_from_db->getIndexedCustomData3() . "'.&#xD;Click 'Add to Video' to get started";
			$vshow_from_db->initFromTemplate ( $vuser_id ,$sample_text );
*/
			// be sure to save the $vshow_from_db and NOT $vshow - this will create a new entry in the DB
			$vshow_from_db->save();
			
			// update the name of the roughcut too
			$show_entry_id = $vshow_from_db->getShowEntryId();
			$showEntry = entryPeer::retrieveByPK($show_entry_id);
			if ($showEntry)
			{
				$showEntry->setName($vshow_from_db->getName());
				$showEntry->save();
			}


			// TODO - decide which of the notifications should be called
			myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_VSHOW_UPDATE_INFO , $vshow_from_db );
			// or
			//myNotificationMgr::createNotification( notification::NOTIFICATION_TYPE_VSHOW_UPDATE_PERMISSIONS , $vshow_from_db );

//			$this->addDebug ( "timer_db_save" , ( microtime( true ) - $timer ) );


			$end_obj_creation = microtime( true );
			$this->addDebug ( "obj_creation_time" , ( $end_obj_creation - $start_obj_creation ) );
		}
		else
		{
			$vshow_from_db = $vshow;
			// no fiends to update !
		}


		// see if trying to rollback
		$desired_version = $this->getP ( "vshow_version");
		if ( $desired_version )
		{
			$result = $vshow_from_db->rollbackVersion ( $desired_version );

			if ( ! $result )
			{
				$this->addError ( APIErrors::ERROR_VSHOW_ROLLBACK , $vshow_id , $desired_version);
			}
		}

		$this->addMsg ( "vshow" , objectWrapperBase::getWrapperClass( $vshow_from_db , objectWrapperBase::DETAIL_LEVEL_REGULAR ) );
		$this->addDebug ( "modified_fields" , $fields_modified );

	}
}
?>
