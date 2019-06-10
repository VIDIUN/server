<?php
/**
 * @package api
 * @subpackage ps2
 */
class resetadminpasswordAction extends defPartnerservices2Action
{
	public function describe()
	{
		return
			array (
				"display_name" => "resetAdminPassword",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array (
						"email" => array ("type" => "string", "desc" => "") , 
						
						),
					"optional" => array (
						)
					),
				"out" => array (
					"new_password" => array ( "type" => "string" , "desc" => "" ),
					),
				"errors" => array (
					)
			);
	}

	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		defPartnerservices2baseAction::disableCache();
		
		$email = trim ( $this->getPM ( "email" ) );
		try {	
			$new_password = UserLoginDataPeer::resetUserPassword ( $email  );
		}		
		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::LOGIN_DATA_NOT_FOUND) {
				$this->addException( APIErrors::ADMIN_VUSER_NOT_FOUND );
				return null;
			}
			if ($code == vUserException::PASSWORD_STRUCTURE_INVALID) {
				$this->addException( APIErrors::PASSWORD_STRUCTURE_INVALID );
				return null;
			}
			if ($code == vUserException::PASSWORD_ALREADY_USED) {
				$this->addException( APIErrors::PASSWORD_ALREADY_USED );
				return null;
			}
			if ($code == vUserException::INVALID_EMAIL) {
				$this->addException( APIErrors::INVALID_FIELD_VALUE, 'email' );
				return null;
			}
			if ($code == vUserException::LOGIN_ID_ALREADY_USED) {
				$this->addException( APIErrors::LOGIN_ID_ALREADY_USED);
				return null;
			}			
			throw $e;
		}
		
		if ( ! $new_password )
		{
			$this->addException( APIErrors::ADMIN_VUSER_NOT_FOUND );
		}
		$this->addMsg ( "msg" , "email sent") ;
	}
}
?>