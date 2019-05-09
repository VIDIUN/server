<?php
/**
 * @package api
 * @subpackage ps2
 */
class setmetadataAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "setMetaData",
				"desc" => "",
				"in" => array (
					"mandatory" => array ( 
						"entry_id" => array ("type" => "string", "desc" => ""),
						"vshow_id" => array ("type" => "string", "desc" => ""),
						"HasRoughCut" => array ("type" => "boolean", "desc" => ""),
						"xml" => array ("type" => "xml", "desc" => "")
						),
					"optional" => array (
						)
					),
				"out" => array (
					"saved_entry" => array ("type" => "string", "desc" => ""),
					"xml" => array ("type" => "xml", "desc" => "")
					),
				"errors" => array (
					APIErrors::INVALID_VSHOW_ID , 
					APIErrors::INVALID_ENTRY_ID ,				
				)
			); 
	}
	
	public function addUserOnDemand ( )		{	return self::CREATE_USER_FORCE;	}
	public function needVuserFromPuser ( )	{	return self::VUSER_DATA_VUSER_ID_ONLY;	}
	public function requiredPrivileges () 	{ 	return "edit:<vshow_id>" ; }

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$entry_id = $this->getP ( "entry_id" );
		$vshow_id =  $this->getP ( "vshow_id" );
		
		list ( $vshow , $entry , $error , $error_obj ) = myVshowUtils::getVshowAndEntry( $vshow_id  , $entry_id );

		if ( $error_obj )
		{
			$this->addError ( $error_obj );
			return ;
		}

		$vshow_id = $vshow->getId();

		if ($vshow_id === vshow::SANDBOX_ID)
		{
			$this->addError ( APIErrors::SANDBOX_ALERT );
			return ;
		}
		
		// TODO -  think what is the best way to verify the privileges - names and parameters that are initially set by the partner at
		// startsession time
		if ( ! $this->isOwnedBy ( $vshow , $puser_vuser->getVuserId() ) )
			$this->verifyPrivileges ( "edit" , $vshow_id ); // user was granted explicit permissions when initiatd the vs

		// this part overhere should be in a more generic place - part of the services
		$multiple_roghcuts = Partner::allowMultipleRoughcuts( $partner_id );
		$livuser_id = $puser_vuser->getVuserId();

		$isIntro = $vshow->getIntroId() == $entry->getId();

		if ( $multiple_roghcuts )
		{
			// create a new entry in two cases:
			// 1. the user saving the roughcut isnt the owner of the entry
			// 2. the entry is an intro and the current entry is not show (probably an image or video)
			if ($entry->getVuserId() != $livuser_id || $isIntro && $entry->getMediaType() != entry::ENTRY_MEDIA_TYPE_SHOW)
			{
				// TODO: add security check to whether multiple roughcuts are allowed

				// create a new roughcut entry by cloning the original entry
				$entry = myEntryUtils::deepClone($entry, $vshow_id, false);
				$entry->setVuserId($livuser_id);
				$entry->setCreatorVuserId($puser_vuser->getVuserId() );
				$entry->setCreatedAt(time());
				$entry->setMediaType(entry::ENTRY_MEDIA_TYPE_SHOW);
				$entry->save();
			}
		}

		$xml_content = "<xml><EntryID>".$entry->getId()."</EntryID></xml>";

		if ($isIntro)
		{
			$vshow->setIntroId($entry->getId());
		}
		else
		{
			$vshow->setShowEntryId($entry->getId());
			$has_roughcut = $this->getP ( "HasRoughCut" , "1" , true );
			if ( $has_roughcut === "0" )
			{
				$vshow->setHasRoughcut( false) ;
				$vshow->save();
				$this->addMsg ( "saved_entry" , $entry->getId() );
				return ;
			}
		}

		$content = $this->getP ( "xml" );
		$update_vshow = false;

		if ( $content != NULL )
		{
			$version_info = array();
			$version_info["VuserId"] = $puser_vuser->getVuserId();
			$version_info["PuserId"] = $puser_id;
			$version_info["ScreenName"] = $puser_vuser->getPuserName();

			list($xml_content, $comments, $update_vshow) = myMetadataUtils::setMetadata($content, $vshow, $entry, false, $version_info);
		}
		else
		{
			$comments = "";
			// if there is no xml - receive it from the user
			$this->debug=true;
			return "text/html; charset=utf-8";
		}

		$this->addMsg ( "xml" , $xml_content );
		$this->addMsg ( "saved_entry" , $entry->getId() );
		$this->addDebug ( "comment" , $comments );

	}
}
?>