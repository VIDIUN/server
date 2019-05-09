<?php
/**
 * @package    Core
 * @subpackage VMC
 */
class extloginAction extends vidiunAction
{
	
	private function dieOnError($error_code)
	{
		if ( is_array ( $error_code ) )
		{
			$args = $error_code;
			$error_code = $error_code[0];
		}
		else
		{
			$args = func_get_args();
		}
		array_shift($args);
		
		$errorData = APIErrors::getErrorData( $error_code, $args );
		$error_code = $errorData['code'];
		$formated_desc = $errorData['message'];
		
		header("X-Vidiun:error-$error_code");
		header('X-Vidiun-App: exiting on error '.$error_code.' - '.$formated_desc);
		
		die();
	}
	
	public function execute()
	{
		$vs = $this->getP ( "vs" );
		if(!$vs)
			$this->dieOnError  ( APIErrors::MISSING_VS );
			
		$requestedPartnerId = $this->getP ( "partner_id" );
		
		$expired = $this->getP ( "exp" );

		$vsObj = vSessionUtils::crackVs($vs);
		$vsPartnerId = $vsObj->partner_id;

		if($vsObj->hasPrivilege(vSessionBase::PRIVILEGE_ENABLE_PARTNER_CHANGE_ACCOUNT) &&
			!$vsObj->verifyPrivileges(vSessionBase::PRIVILEGE_ENABLE_PARTNER_CHANGE_ACCOUNT, $requestedPartnerId))
			$this->dieOnError  ( APIErrors::PARTNER_CHANGE_ACCOUNT_DISABLED );

		if (!$requestedPartnerId) {
			$requestedPartnerId = $vsPartnerId;
		}
		
		try {
			$adminVuser = UserLoginDataPeer::userLoginByVs($vs, $requestedPartnerId, true);
		}
		catch (vUserException $e) {
			$code = $e->getCode();
			if ($code == vUserException::USER_NOT_FOUND) {
				$this->dieOnError  ( APIErrors::ADMIN_VUSER_NOT_FOUND );
			}
			if ($code == vUserException::LOGIN_DATA_NOT_FOUND) {
				$this->dieOnError  ( APIErrors::ADMIN_VUSER_NOT_FOUND );
			}
			else if ($code == vUserException::LOGIN_RETRIES_EXCEEDED) {
				$this->dieOnError  ( APIErrors::LOGIN_RETRIES_EXCEEDED );
			}
			else if ($code == vUserException::LOGIN_BLOCKED) {
				$this->dieOnError  ( APIErrors::LOGIN_BLOCKED );
			}
			else if ($code == vUserException::PASSWORD_EXPIRED) {
				$this->dieOnError  ( APIErrors::PASSWORD_EXPIRED );
			}
			else if ($code == vUserException::WRONG_PASSWORD) {
				$this->dieOnError  (APIErrors::ADMIN_VUSER_NOT_FOUND);
			}
			else if ($code == vUserException::USER_IS_BLOCKED) {
				$this->dieOnError  (APIErrors::USER_IS_BLOCKED);
			}
			$this->dieOnError  ( APIErrors::INTERNAL_SERVERL_ERROR );
		}
		if (!$adminVuser || !$adminVuser->getIsAdmin()) {
			$this->dieOnError  ( APIErrors::ADMIN_VUSER_NOT_FOUND );
		}
		
		
		if ($requestedPartnerId != $adminVuser->getPartnerId()) {
			$this->dieOnError  ( APIErrors::UNKNOWN_PARTNER_ID );
		}
		
		$partner = PartnerPeer::retrieveByPK( $adminVuser->getPartnerId() );
		
		if (!$partner)
		{
			$this->dieOnError  ( APIErrors::UNKNOWN_PARTNER_ID );
		}
		
		if (!$partner->validateApiAccessControl())
		{
			$this->dieOnError  ( APIErrors::SERVICE_ACCESS_CONTROL_RESTRICTED );
		}
		
		$partner_id = $partner->getId();
		$subp_id = $partner->getSubpId() ;
		$admin_puser_id = $adminVuser->getPuserId();
		
		$exp = (isset($expired) && is_numeric($expired)) ? time() + $expired: 0;
		
		$noUserInVs = is_null($vsObj->user) || $vsObj->user === '';
		if ( ($vsPartnerId != $partner_id) || ($partner->getVmcVersion() >= 4 && $noUserInVs) )
		{
			$vs = null;
			$sessionType = $adminVuser->getIsAdmin() ? SessionType::ADMIN : SessionType::USER;
			$privileges =  "*," . vSessionBase::PRIVILEGE_DISABLE_ENTITLEMENT;
			if($vsObj->hasPrivilege(vSessionBase::PRIVILEGE_ENABLE_PARTNER_CHANGE_ACCOUNT))
				$privileges = $privileges.",".vSessionBase::PRIVILEGE_ENABLE_PARTNER_CHANGE_ACCOUNT.":".
					implode(vSessionBase::PRIVILEGES_DELIMITER, $vsObj->getPrivilegeValues(vSessionBase::PRIVILEGE_ENABLE_PARTNER_CHANGE_ACCOUNT));

			vSessionUtils::createVSessionNoValidations ( $partner_id ,  $admin_puser_id , $vs , 30 * 86400 , $sessionType , "" , $privileges );
		}
		
		
		$path = "/";
		$domain = null;
		$force_ssl = PermissionPeer::isValidForPartner(PermissionName::FEATURE_VMC_ENFORCE_HTTPS, $partner_id);
		$secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' && $force_ssl) ? true : false;
		$http_only = true;
		
		$this->getResponse()->setCookie("pid", $partner_id, $exp, $path, $domain, $secure, $http_only);
		$this->getResponse()->setCookie("subpid", $subp_id, $exp, $path, $domain, $secure, $http_only);
		$this->getResponse()->setCookie("vmcvs", $vs, $exp, $path, $domain, $secure, $http_only);

		$redirect_url =  ($force_ssl) ? 'https' : 'http';
		$redirect_url .= '://' . $_SERVER["HTTP_HOST"] . '/index.php/vmc/vmc2';
		$this->redirect($redirect_url);
	}
	
}
