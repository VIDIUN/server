<?php
/**
 * @package api
 * @subpackage ps2
 */
class adduserAction extends defPartnerservices2Action
{
	public function describe()
	{
		return 
			array (
				"display_name" => "addUser",
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
					"user" => array ("type" => "vuser", "desc" => "")
					),
				"errors" => array (
				)
			); 
	}
	
	protected function ticketType()	{		return self::REQUIED_TICKET_ADMIN;	}
	
	public function needVuserFromPuser ( )	{		return self::VUSER_DATA_NO_VUSER; 	}
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		$target_puser_id = $this->getPM ( "user_id" );
		
		$target_puser_vuser = PuserVuserPeer::retrieveByPartnerAndUid( $partner_id , null , $target_puser_id );
		
		if ( $target_puser_vuser )
		{
			$this->addDebug ( "puser_exists" , $target_puser_vuser->getId() );
			
			// might be that the puser_vuser exists but the vuser does not
			$vuser = vuserPeer::retrieveByPK( $target_puser_vuser->getVuserId() );
			if ( $vuser )
			{
				$this->addError ( APIErrors::DUPLICATE_USER_BY_ID , $target_puser_id );
				return;
			}
			else
			{
				// puser_vuser exists but need to create the vsuer...
			}
		}
		else
		{
			$target_puser_vuser = new PuserVuser();
			$target_puser_vuser->setPuserId ( $target_puser_id );
			$target_puser_vuser->setPartnerId( $partner_id );
			$target_puser_vuser->save();
			
			$this->addDebug ( "Created_new_puser_vuser" , $target_puser_vuser->getId() );
		}
		
		// get the new properties for the vuser from the request
		$vuser = new vuser();
		
		$obj_wrapper = objectWrapperBase::getWrapperClass( $vuser , 0 );
		
		$fields_modified = baseObjectUtils::fillObjectFromMap ( $this->getInputParams() , $vuser , "user_" , $obj_wrapper->getUpdateableFields() );
		// check that mandatory fields were set
		// TODO
		if ( count ( $fields_modified ) > 0 )
		{
			if (!$partner_id) // is this a partner policy we should enforce?
			{
				$vuser_from_db = vuserPeer::getVuserByScreenName ( $vuser->getScreenName() );
				if ( $vuser_from_db )
				{
					$this->addError( APIErrors::DUPLICATE_USER_BY_SCREEN_NAME , $vuser->getScreenName() );
					return;
				}
			}
			
			$vuser->setPartnerId( $partner_id );
			$vuser->setPuserId($target_puser_id);
			
			try {
				$vuser = vuserPeer::addUser($vuser);
			}
			catch (vUserException $e) {
				$code = $e->getCode();
				if ($code == vUserException::USER_ALREADY_EXISTS) {
					$this->addException( APIErrors::DUPLICATE_USER_BY_ID, $vuser->getId() );
					return null;
				}
				if ($code == vUserException::LOGIN_ID_ALREADY_USED) {
					$this->addException( APIErrors::DUPLICATE_USER_BY_LOGIN_ID , $vuser->getEmail());
					return null;
				}
				else if ($code == vUserException::USER_ID_MISSING) {
					$this->addException( APIErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, 'id' );
					return null;
				}
				else if ($code == vUserException::INVALID_EMAIL) {
					$this->addException( APIErrors::INVALID_FIELD_VALUE );
					return null;
				}
				else if ($code == vUserException::INVALID_PARTNER) {
					$this->addException( APIErrors::UNKNOWN_PARTNER_ID );
					return null;
				}
				else if ($code == vUserException::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED) {
					$this->addException( APIErrors::ADMIN_LOGIN_USERS_QUOTA_EXCEEDED );
					return null;
				}
				else if ($code == vUserException::PASSWORD_STRUCTURE_INVALID) {
					$this->addException( APIErrors::PASSWORD_STRUCTURE_INVALID );
					return null;
				}
				throw $e;			
			}
			catch (vPermissionException $e)
			{
				$code = $e->getCode();
				if ($code == vPermissionException::ROLE_ID_MISSING) {
					$this->addException( APIErrors::ROLE_ID_MISSING );
					return null;
				}
				if ($code == vPermissionException::ONLY_ONE_ROLE_PER_USER_ALLOWED) {
					$this->addException( APIErrors::ONLY_ONE_ROLE_PER_USER_ALLOWED );
					return null;
				}
				throw $e;
			}	
						
			// now update the puser_vuser
			$target_puser_vuser->setPuserName( $vuser->getScreenName() );
			$target_puser_vuser->setVuserId( $vuser->getId() );
			$target_puser_vuser->save();
			
			$this->addMsg ( "user" , objectWrapperBase::getWrapperClass( $target_puser_vuser , objectWrapperBase::DETAIL_LEVEL_DETAILED) );
			$this->addDebug ( "added_fields" , $fields_modified );
			
		}
		else
		{
			$this->addError( APIErrors::NO_FIELDS_SET_FOR_USER );
		}
		

	}
}
?>