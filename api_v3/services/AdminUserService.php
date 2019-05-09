<?php
/**
 * Manage details for the administrative user
 *
 * @service adminUser
 * @package api
 * @subpackage services
 * @deprecated use user service instead
 */
class AdminUserService extends VidiunBaseUserService 
{
	
	protected function partnerRequired($actionName)
	{
		if ($actionName === 'updatePassword') {
			return false;
		}
		if ($actionName === 'resetPassword') {
			return false;
		}
		if ($actionName === 'login') {
			return false;
		}
		if ($actionName === 'setInitialPassword') {
			return false;
		}
		return parent::partnerRequired($actionName);
	}	
	

	/**
	 * keep backward compatibility with changed error codes
	 * @param VidiunAPIException $e
	 * @throws VidiunAPIException
	 */
	private function throwTranslatedException(VidiunAPIException $e)
	{
		$code = $e->getCode();
		if ($code == VidiunErrors::USER_NOT_FOUND) {
			throw new VidiunAPIException(VidiunErrors::ADMIN_VUSER_NOT_FOUND);
		}
		else if ($code == VidiunErrors::WRONG_OLD_PASSWORD) {
			throw new VidiunAPIException(VidiunErrors::ADMIN_VUSER_WRONG_OLD_PASSWORD, "wrong password" );
		}
		else if ($code == VidiunErrors::USER_WRONG_PASSWORD) {
			throw new VidiunAPIException(VidiunErrors::ADMIN_VUSER_NOT_FOUND);
		}
		else if ($code == VidiunErrors::LOGIN_DATA_NOT_FOUND) {
			throw new VidiunAPIException(VidiunErrors::ADMIN_VUSER_NOT_FOUND);
		}
		throw $e;
	}
	
	
	/**
	 * Update admin user password and email
	 * 
	 * @action updatePassword
	 * @param string $email
	 * @param string $password
	 * @param string $newEmail Optional, provide only when you want to update the email
	 * @param string $newPassword
	 * @return VidiunAdminUser
	 * @vsIgnored
	 *
	 * @throws VidiunErrors::INVALID_FIELD_VALUE
	 * @throws VidiunErrors::ADMIN_VUSER_WRONG_OLD_PASSWORD
	 * @throws VidiunErrors::ADMIN_VUSER_NOT_FOUND
	 * @throws VidiunErrors::PASSWORD_STRUCTURE_INVALID
	 * @throws VidiunErrors::PASSWORD_ALREADY_USED
	 * @throws VidiunErrors::INVALID_FIELD_VALUE
	 * @throws VidiunErrors::LOGIN_ID_ALREADY_USED
	 * 
	 * @deprecated
	 */
	public function updatePasswordAction( $email , $password , $newEmail = "" , $newPassword = "" )
	{
		try
		{
			parent::updateLoginDataImpl($email, $password, $newEmail, $newPassword);
			
			// copy required parameters to a VidiunAdminUser object for backward compatibility
			$adminUser = new VidiunAdminUser();
			$adminUser->email = $newEmail ? $newEmail : $email;
			$adminUser->password = $newPassword ? $newPassword : $password;
			
			return $adminUser;
		}
		catch (VidiunAPIException $e) // keep backward compatibility with changed error codes
		{
			$this->throwTranslatedException($e);
		}
	}
	
	
	/**
	 * Reset admin user password and send it to the users email address
	 * 
	 * @action resetPassword
	 * @param string $email
	 * @vsIgnored
	 *
	 * @throws VidiunErrors::ADMIN_VUSER_NOT_FOUND
	 * @throws VidiunErrors::PASSWORD_STRUCTURE_INVALID
	 * @throws VidiunErrors::PASSWORD_ALREADY_USED
	 * @throws VidiunErrors::INVALID_FIELD_VALUE
	 * @throws VidiunErrors::LOGIN_ID_ALREADY_USED
	 */	
	public function resetPasswordAction($email)
	{
		try
		{
			return parent::resetPasswordImpl($email);
		}
		catch (VidiunAPIException $e) // keep backward compatibility with changed error codes
		{
			$this->throwTranslatedException($e);
		}
	}
	
	/**
	 * Get an admin session using admin email and password (Used for login to the VMC application)
	 * 
	 * @action login
	 * @param string $email
	 * @param string $password
	 * @param int $partnerId
	 * @return string
	 * @vsIgnored
	 *
	 * @throws VidiunErrors::ADMIN_VUSER_NOT_FOUND
	 * @thrown VidiunErrors::INVALID_PARTNER_ID
	 * @thrown VidiunErrors::LOGIN_RETRIES_EXCEEDED
	 * @thrown VidiunErrors::LOGIN_BLOCKED
	 * @thrown VidiunErrors::PASSWORD_EXPIRED
	 * @thrown VidiunErrors::INVALID_PARTNER_ID
	 * @thrown VidiunErrors::INTERNAL_SERVERL_ERROR
	 */		
	public function loginAction($email, $password, $partnerId = null)
	{
		try
		{
			$vs = parent::loginImpl(null, $email, $password, $partnerId);
			$tempVs = vSessionUtils::crackVs($vs);
			if (!$tempVs->isAdmin()) {
				throw new VidiunAPIException(VidiunErrors::ADMIN_VUSER_NOT_FOUND); 
			}
			return $vs;
		}
		catch (VidiunAPIException $e) // keep backward compatibility with changed error codes
		{
			$this->throwTranslatedException($e);
		}
	}
	
	
	
	/**
	 * Set initial users password
	 * 
	 * @action setInitialPassword
	 * @param string $hashKey
	 * @param string $newPassword new password to set
	 * @vsIgnored
	 *
	 * @throws VidiunErrors::ADMIN_VUSER_NOT_FOUND
	 * @throws VidiunErrors::PASSWORD_STRUCTURE_INVALID
	 * @throws VidiunErrors::NEW_PASSWORD_HASH_KEY_EXPIRED
	 * @throws VidiunErrors::NEW_PASSWORD_HASH_KEY_INVALID
	 * @throws VidiunErrors::PASSWORD_ALREADY_USED
	 * @throws VidiunErrors::INTERNAL_SERVERL_ERROR
	 */	
	public function setInitialPasswordAction($hashKey, $newPassword)
	{
		try
		{
			return parent::setInitialPasswordImpl($hashKey, $newPassword);
		}
		catch (VidiunAPIException $e) // keep backward compatibility with changed error codes
		{
			$this->throwTranslatedException($e);
		}
	}
	
	
}