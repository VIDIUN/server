<?php
/**
 * @package    Core
 * @subpackage VMC
 */
class vmcAction extends vidiunAction
{
	const BASE64_ENCODE_CHARS_REGEX = "/^[a-zA-Z0-9\/\+\=]+$/";
	
	public function execute ( ) 
	{
		// Prevent the page fron being embeded in an iframe
		header( 'X-Frame-Options: DENY' );

		// Check if user already logged in and redirect to vmc2
		if( $this->getRequest()->getCookie('vmcvs') ) {
			$this->redirect('vmc/vmc2');
		}

		if ((infraRequestUtils::getProtocol() != infraRequestUtils::PROTOCOL_HTTPS) && vConf::get('vmc_secured_login'))
		{
			$url = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
			header('Location:' . $url);
			die;
		}

		$this->www_host = vConf::get('www_host');
		$https_enabled = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? true : false;
		$this->securedLogin = (vConf::get('vmc_secured_login') || $https_enabled) ? true : false;

		$swfUrl = ($this->securedLogin) ? 'https://' : 'http://';
		$swfUrl .= $this->www_host . myContentStorage::getFSFlashRootPath ();
		$swfUrl .= '/vmc/login/' . vConf::get('vmc_login_version') . '/login.swf';
		$this->swfUrl = $swfUrl;

		$this->partner_id = $this->getRequestParameter( "partner_id" );
		$this->logoUrl = null;
		if ( $this->partner_id ) {
			$partner = PartnerPeer::retrieveByPK($this->partner_id);
			if( $partner ){
				$this->logoUrl = vmcUtils::getWhitelabelData( $partner, 'logo_url' );
			}
		}
		
		$this->beta = $this->getRequestParameter( "beta" );

		//prevent script injections - allow only base64_encode chars , which is used when creating A new hash key
		$passHashparam = $this->getRequestParameter( "setpasshashkey" );
		if ($passHashparam && !preg_match(self::BASE64_ENCODE_CHARS_REGEX , $passHashparam))
			VExternalErrors::dieError(VExternalErrors::INVALID_HASH);

		$this->setPassHashKey = $passHashparam;

		$this->hashKeyErrorCode = null;
		$this->displayErrorFromServer = false;
		if ($this->setPassHashKey) {
			try {
				$loginData = UserLoginDataPeer::isHashKeyValid($this->setPassHashKey);
				$partnerId = $loginData->getConfigPartnerId();
				$partner = PartnerPeer::retrieveByPK($partnerId);
				if ($partner && $partner->getPasswordStructureValidations())
					$this->displayErrorFromServer = true;  			
				
			}
			catch (vCoreException $e) {
				$this->hashKeyErrorCode = $e->getCode();
			}
		}		
		sfView::SUCCESS;
	}
}
