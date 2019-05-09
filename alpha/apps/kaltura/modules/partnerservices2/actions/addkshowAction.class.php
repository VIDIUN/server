<?php
/**
 * @package api
 * @subpackage ps2
 */
class addvshowAction extends defPartnerservices2Action
{
	public function describe()
	{
		return
			array (
				"display_name" => "addVShow",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array (
						"vshow" 				=> array ("type" => "vshow", "desc" => "vshow"),
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
					APIErrors::DUPLICATE_VSHOW_BY_NAME
				)
			);
	}
/*
	protected function ticketType ()
	{
		return self::REQUIED_TICKET_ADMIN;
	}
*/
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
		$vshows_from_db = null;
		// works in one of 2 ways:
		// 1. get no requested name - will create a new vshow and return its details
		// 2. get some name - tries to fetch by name. if already exists - return it

		// get the new properties for the vuser from the request
		$vshow = new vshow();

		$allow_duplicate_names = $this->getP ( "allow_duplicate_names" , true , true );
		if ( $allow_duplicate_names === "false" || $allow_duplicate_names === 0 ) $allow_duplicate_names = false;

		$return_metadata = $this->getP ( "metadata" , false );
		$detailed = $this->getP ( "detailed" , false );
		$level = ( $detailed ? objectWrapperBase::DETAIL_LEVEL_DETAILED : objectWrapperBase::DETAIL_LEVEL_REGULAR );

		$obj_wrapper = objectWrapperBase::getWrapperClass( $vshow , 0 );

		$fields_modified = baseObjectUtils::fillObjectFromMap ( $this->getInputParams() , $vshow , "vshow_" , $obj_wrapper->getUpdateableFields() );
		// check that mandatory fields were set
		// TODO
		$vshow->setName( trim ( $vshow->getName() ) );
		// ASSUME - the name is UNIQUE per partner_id !

		if ( $vshow->getName() )
		{
			if ( myPartnerUtils::shouldForceUniqueVshow( $partner_id , $allow_duplicate_names ) )
			{
				// in this case willsearch for an existing vshow with this name and return with an error if found
				$vshows_from_db = vshowPeer::getVshowsByName ( trim ( $vshow->getName() ) );
				if ( $vshows_from_db )
				{
					$vshow_from_db = $vshows_from_db[0];
					$this->addDebug ( "already_exists_objects" , count ( $vshows_from_db ) );
					$this->addError ( APIErrors::DUPLICATE_VSHOW_BY_NAME, $vshow->getName() ) ;// This field in unique. Please change ");
					if( myPartnerUtils::returnDuplicateVshow( $partner_id ))
					{
						$this->addMsg ( "vshow" , objectWrapperBase::getWrapperClass( $vshow_from_db , $level  ) );
					}
					return;
				}
			}
		}


		// the first vuser to create this vshow will be it's producer
		$producer_id =   $puser_vuser->getVuserId();
		$vshow->setProducerId( $producer_id );
		// moved to the update - where there is

		$vshow->setPartnerId( $partner_id );
		$vshow->setSubpId( $subp_id );
		$vshow->setViewPermissions( vshow::VSHOW_PERMISSION_EVERYONE );

		// by default the permissions should be public
		if ( $vshow->getPermissions () === null )
		{ 
			$vshow->setPermissions( vshow::PERMISSIONS_PUBLIC );
		}
		
		// have to save the vshow before creating the default entries
		$vshow->save();
		$show_entry = $vshow->createEntry( entry::ENTRY_MEDIA_TYPE_SHOW , $producer_id , "&auto_edit.jpg" , $vshow->getName() ); // roughcut
		$vshow->createEntry( entry::ENTRY_MEDIA_TYPE_VIDEO , $producer_id ); // intro
/*
		$sample_text = $vshow->getName();
		$host = requestUtils::getHost();
*/
		$sample_text = "";
		myEntryUtils::modifyEntryMetadataWithText ( $show_entry , $sample_text , "" );

		// set the roughcut to false so the update iwll override with better data
		$vshow->setHasRoughcut( false );

		$vshow->initFromTemplate ( $producer_id , $sample_text);

		$vshow->save();

		myNotificationMgr::createNotification( vNotificationJobData::NOTIFICATION_TYPE_VSHOW_ADD , $vshow );

		$this->addMsg ( "vshow" , objectWrapperBase::getWrapperClass( $vshow ,  $level  ) );

		if ( $return_metadata )
		{
			$this->addMsg ( "metadata" , $vshow->getMetadata() );
		}

		$this->addDebug ( "added_fields" , $fields_modified );
		if ( $vshows_from_db )
			$this->addDebug ( "already_exists_objects" , count ( $vshows_from_db ) );

	}
}
?>