<?php
/**
 * @package api
 * @subpackage ps2
 */
class updateuseridAction extends defPartnerservices2Action
{
	public function describe()
	{
		return
			array (
				"display_name" => "updateUserId",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array (
						"user_id" => array ("type" => "string", "desc" => ""),
						"new_user_id" => array ("type" => "string", "desc" => ""),
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
	public function needVuserFromPuser ( ) 	{ 		return self::VUSER_DATA_NO_VUSER; 	}

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$user_id = $this->getPM ( "user_id" );
		$new_user_id = $this->getPM ( "new_user_id" );
		
		$target_puser_vuser = PuserVuserPeer::retrieveByPartnerAndUid($partner_id , null /* $subp_id */, $user_id , true );
		
		if ( ! $target_puser_vuser )
		{
			$this->addError ( APIErrors::INVALID_USER_ID , $user_id );
			return;
		}
		
		$new_puser_vuser = PuserVuserPeer::retrieveByPartnerAndUid($partner_id , null /* $subp_id */ , $new_user_id , true );
		$vuser = vuserPeer::getVuserByPartnerAndUid($partner_id, $new_user_id);
		
		if ( $new_puser_vuser || $vuser)
		{
			$this->addError ( APIErrors::DUPLICATE_USER_BY_ID , $new_user_id );
			return;
		}
		
		$target_puser_vuser->setPuserId( $new_user_id );
		$target_puser_vuser->save();
		
		PuserVuserPeer::removeFromCache($target_puser_vuser);
		
		$vuser = $target_puser_vuser->getVuser();
		$vuser->setPuserId($target_puser_vuser->getPuserId());
		$vuser->save();
		
		$wrapper = objectWrapperBase::getWrapperClass( $target_puser_vuser , objectWrapperBase::DETAIL_LEVEL_DETAILED);
		$wrapper->removeFromCache( "PuserVuser" , $target_puser_vuser->getId() );
		
		$this->addMsg ( "user" , $wrapper );
	}
}
