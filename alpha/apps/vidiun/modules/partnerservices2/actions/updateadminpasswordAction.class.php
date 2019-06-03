<?php
/**
 * @package api
 * @subpackage ps2
 */
class updateadminpasswordAction extends defPartnerservices2Action
{
	public function describe()
	{
		return
			array (
				"display_name" => "updateAdminPassword",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array (
						"adminVuser_email" => array ("type" => "string", "desc" => "") ,
						"adminVuser_password" => array ("type" => "string", "desc" => "") ,
						"new_password" => array ("type" => "string", "desc" => "") ,
						),
					"optional" => array (
						"new_email" => array ("type" => "string", "desc" => "") ,			
						)
					),
				"out" => array (
					"new_password" => array ( "type" => "string" , "desc" => "" ),
					),
				"errors" => array (
					APIErrors::INVALID_FIELD_VALUE,
					APIErrors::ADMIN_VUSER_NOT_FOUND,
					)
			);
	}

    
	public function executeImpl ( $partner_id , $subp_id , $puser_id , $partner_prefix , $puser_vuser )
	{
		defPartnerservices2baseAction::disableCache();
		
		$email = trim ( $this->getPM ( "adminVuser_email" ) );
		$new_email = trim ( $this->getP ( "new_email" ) );
		$old_password = trim (  $this->getPM ( "adminVuser_password" , null ) );
		$password = trim (  $this->getPM ( "new_password" , null ) );
		
		if ( $new_email )
		{
			if(!vString::isEmailString($new_email))
			{
				$f_name = "new_email";
				$this->addException( APIErrors::INVALID_FIELD_VALUE, $f_name );
			}
		}
		try {
			UserLoginDataPeer::updateLoginData ( $email , $old_password, $new_email, $password );
		}
		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::LOGIN_DATA_NOT_FOUND) {
				$this->addException( APIErrors::ADMIN_VUSER_NOT_FOUND );
				return null;
			}
			if ($code == vUserException::WRONG_PASSWORD) {
				$this->addException( APIErrors::ADMIN_VUSER_WRONG_OLD_PASSWORD );
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
				$this->addException( APIErrors::INVALID_FIELD_VALUE, 'new_email' );
				return null;
			}
			if ($code == vUserException::LOGIN_ID_ALREADY_USED) {
				$this->addException( APIErrors::LOGIN_ID_ALREADY_USED);
				return null;
			}
			throw $e;
		}	

		if ( $new_email )
		{
			$this->addMsg ( "new_email" , $new_email ) ;
		}
		$this->addMsg ( "new_password" , $password ) ;
	}
}
?>