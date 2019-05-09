<?php
/**
 * @package api
 * @subpackage ps2
 */
class updateuserAction extends defPartnerservices2Action
{
	public function describe()
	{
		return
			array (
				"display_name" => "updateUser",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array (
						"user_id" => array ("type" => "integer", "desc" => ""),
						"user" => array ("type" => "vuser", "desc" => "")
						),
					"optional" => array (
						)
					),
				"out" => array (
					"user" => array ("type" => "PuserVuser", "desc" => "")
					),
				"errors" => array (
					APIErrors::INVALID_USER_ID , 
					APIErrors::USER_ALREADY_EXISTS_BY_SCREEN_NAME ,
				)
			);
	}

	protected function ticketType()	{		return self::REQUIED_TICKET_ADMIN;	}

	// ask to fetch the vuser from puser_vuser
	public function needVuserFromPuser ( ) 	{ 		return self::VUSER_DATA_VUSER_DATA; 	}

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{

		$user_id = $this->getPM ( "user_id" );
		$target_puser_vuser = PuserVuserPeer::retrieveByPartnerAndUid($partner_id , null , $user_id , true );

		if ( ! $target_puser_vuser )
		{
			$this->addError ( APIErrors::INVALID_USER_ID , $user_id );
		}

		$vuser = $target_puser_vuser->getVuser();

		// get the new properties for the vuser from the request
		$vuser_update_data = new vuser();

		$obj_wrapper = objectWrapperBase::getWrapperClass( $vuser , 0 );

		$fields_modified = baseObjectUtils::fillObjectFromMap ( $this->getInputParams() , $vuser_update_data , "user_" , $obj_wrapper->getUpdateableFields() );
		if ( count ( $fields_modified ) > 0 )
		{
			if (!$partner_id) // is this a partner policy we should enforce?
			{
				$vuser_from_db = vuserPeer::getVuserByScreenName ( $vuser->getScreenName() );
				// check if there is a vuser with such a name in the system (and this vuser is not the current one)
				if ( $vuser_from_db && $vuser_from_db->getId() == $vuser->getId() )
				{
					$this->addError( APIErrors::USER_ALREADY_EXISTS_BY_SCREEN_NAME , $vuser->getScreenName() );
					return;
				}
			}

			if ( $vuser_update_data )
			{
				baseObjectUtils::fillObjectFromObject( $obj_wrapper->getUpdateableFields() , $vuser_update_data , $vuser , baseObjectUtils::CLONE_POLICY_PREFER_NEW , null , BasePeer::TYPE_PHPNAME );
				$target_puser_vuser->setVuser ( $vuser );
			}

			$vuser->save();
		}

		$wrapper = objectWrapperBase::getWrapperClass( $target_puser_vuser , objectWrapperBase::DETAIL_LEVEL_DETAILED);
		$wrapper->removeFromCache( "vuser" , $vuser->getId() );
		$this->addMsg ( "user" , $wrapper );
		$this->addDebug ( "modified_fields" , $fields_modified );

	}
}
?>