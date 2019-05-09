<?php
/**
 * @package api
 * @subpackage ps2
 */
class adminloginAction extends defPartnerservices2Action
{
	public function describe()
	{
		return
			array (
				"display_name" => "adminLogin",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array (
						"email" => array ("type" => "string", "desc" => "") , 
						"password" => array ("type" => "string", "desc" => "") ,
						),
					"optional" => array (
						)
					),
				"out" => array (
					"partner_id" => array ( "type" => "string" , "desc" => "" ),
					"subp_id" => array ( "type" => "string" , "desc" => "" ),
					"uid" => array ( "type" => "string" , "desc" => "" ),
					"vs" => array ( "type" => "string" , "desc" => "" ),
					),
				"errors" => array (
					APIErrors::ADMIN_VUSER_NOT_FOUND,
					APIErrors::LOGIN_RETRIES_EXCEEDED,
					APIErrors::LOGIN_BLOCKED,
					APIErrors::USER_WRONG_PASSWORD,
					APIErrors::PASSWORD_EXPIRED,
					APIErrors::UNKNOWN_PARTNER_ID,
					)
			);
	}

    
	protected function shouldCacheResonse () { return false; }
	
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		defPartnerservices2baseAction::disableCache();
		vuserPeer::setUseCriteriaFilter(false);
		
		$email = trim ( $this->getPM ( "email" ) );
		$password = trim (  $this->getPM ( "password" ) );
		
		$loginData = UserLoginDataPeer::getByEmail ($email);
		
		// be sure to return the same error if there are no admins in the list and when there are none matched -
		// so no hint about existing admin will leak 
		if ( !$loginData )
		{
			$this->addError ( APIErrors::ADMIN_VUSER_NOT_FOUND );	
			return;
		}

		try {
			$adminVuser = UserLoginDataPeer::userLoginByEmail($email, $password, $partner_id);
		}
		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::USER_NOT_FOUND) {
				$this->addError  ( APIErrors::ADMIN_VUSER_NOT_FOUND );
				return null;
			}
			if ($code == vUserException::LOGIN_DATA_NOT_FOUND) {
				$this->addError  ( APIErrors::ADMIN_VUSER_NOT_FOUND );
				return null;
			}
			else if ($code == vUserException::LOGIN_RETRIES_EXCEEDED) {
				$this->addError  ( APIErrors::LOGIN_RETRIES_EXCEEDED );
				return null;
			}
			else if ($code == vUserException::LOGIN_BLOCKED) {
				$this->addError  ( APIErrors::LOGIN_BLOCKED );
				return null;
			}
			else if ($code == vUserException::PASSWORD_EXPIRED) {
				$this->addError  ( APIErrors::PASSWORD_EXPIRED );
				return null;
			}
			else if ($code == vUserException::WRONG_PASSWORD) {
				$this->addError  (APIErrors::USER_WRONG_PASSWORD);
				return null;
			}
			else if ($code == vUserException::USER_IS_BLOCKED) {
				$this->addError  (APIErrors::USER_IS_BLOCKED);
				return null;
			}
			else {
				$this->addError  ( APIErrors::INTERNAL_SERVERL_ERROR );
				return null;
			}
		}
		if (!$adminVuser || !$adminVuser->getIsAdmin()) {
			$this->addError  ( APIErrors::ADMIN_VUSER_NOT_FOUND );
			return null;
		}
		
		
		if ($partner_id && $partner_id != $adminVuser->getPartnerId()) {
			$this->addError  ( APIErrors::UNKNOWN_PARTNER_ID );
			return;
		}
		
		$partner = PartnerPeer::retrieveByPK( $adminVuser->getPartnerId() );
		
		if (!$partner)
		{
			$this->addError  ( APIErrors::UNKNOWN_PARTNER_ID );
			return;		
		}
		
		$partner_id = $partner->getId();
		$subp_id = $partner->getSubpId() ;
		$admin_puser_id = $adminVuser->getPuserId();
		
		// get the puser_vuser for this admin if exists, if not - creae it and return it - create a vuser too
		$puser_vuser = PuserVuserPeer::createPuserVuser ( $partner_id , $subp_id, $admin_puser_id , $adminVuser->getScreenName() , $adminVuser->getScreenName(), true);
		$uid = $puser_vuser->getPuserId();
		$vs = null;
		// create a vs for this admin_vuser as if entered the admin_secret using the API
		// ALLOW A VS FOR 30 DAYS
		vSessionUtils::createVSessionNoValidations ( $partner_id ,  $uid , $vs , 30 * 86400 , 2 , "" , "*" );
		
		
		$this->addMsg ( "partner_id" , $partner_id ) ;
		$this->addMsg ( "subp_id" , $subp_id );		
		$this->addMsg ( "uid" , $uid );
		$this->addMsg ( "vs" , $vs );
		$this->addMsg ( "screenName" , $adminVuser->getFullName() );
		$this->addMsg ( "fullName" , $adminVuser->getFullName() );
		$this->addMsg ( "email" , $adminVuser->getEmail() );
	}
}
