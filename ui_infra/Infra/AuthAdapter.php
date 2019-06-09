<?php
/**
 * @package UI-infra
 * @subpackage Authentication
 */
class Infra_AuthAdapter implements Zend_Auth_Adapter_Interface
{
    const SYSTEM_USER_INVALID_CREDENTIALS = 'SYSTEM_USER_INVALID_CREDENTIALS';
    
    const SYSTEM_USER_DISABLED = 'SYSTEM_USER_DISABLED';
    
    const USER_WRONG_PASSWORD = 'USER_WRONG_PASSWORD';
    
    const USER_NOT_FOUND = 'USER_NOT_FOUND';
	
	const X_VIDIUN_REMOTE_ADDR = 'X-VIDIUN-REMOTE-ADDR';
    
	/**
	 * @var string
	 */
	protected $username;
	
	/**
	 * @var string
	 */
	protected $password;
	
	/**
	 * @var string 
	 */
	protected $otp;
	
	/**
	 * @var int
	 */
	protected $partnerId;
	
	/**
	 * @var int
	 */
	protected $timezoneOffset;
	
	/**
	 * @var string
	 */
	protected $privileges = null;
	
	/**
	 * @var string
	 */
	protected $vs;
	
	/**
	 * Sets username and password for authentication
	 */
	public function setCredentials($username, $password = null, $otp = null)
	{
		$this->username = $username;
		$this->password = $password;
		$this->otp = $otp;
	}
	
	/**
	 * Sets vs privileges for authentication
	 */
	public function setPrivileges($privileges)
	{
		$this->privileges = $privileges;
	}
	
	public function setPartnerId($partnerId)
	{
		$this->partnerId = $partnerId;
	}

	public function setTimezoneOffset($timezoneOffset)
	{
		$this->timezoneOffset = $timezoneOffset;
	}

	public function setVS($vs)
	{
		$this->vs = $vs;
	}
	
	/**
	 * @param Vidiun_Client_Type_User $user
	 * @param string $vs
	 * @param int $partnerId
	 *
	 * @return Infra_UserIdentity
	 */
	protected function getUserIdentity(Vidiun_Client_Type_User $user = null, $vs = null, $partnerId = null)
	{
		return new Infra_UserIdentity($user, $vs, $this->timezoneOffset, $partnerId);
	}
	
	/**
	 * Performs an authentication attempt
	 *
	 * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
	 * @return Zend_Auth_Result
	 */
	public function authenticate()
	{
		// Whether the authntication succeeds or fails - generate a fresh session ID
		// This will assist in preventing session hijacking
		// This will also apply session options and cookie updates (e.g. cookie_secure)
		Zend_Session::regenerateId();

		if($this->vs)
		{
			$client = Infra_ClientHelper::getClient();
			$client->setVs($this->vs);
			
    		$user = $client->user->get();
    		/* @var $user Vidiun_Client_Type_User */
    		$identity = $this->getUserIdentity($user, $this->vs, $user->partnerId);
    		return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity);
		}
		
		if (!$this->username || !$this->password)
			return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, null);
		
		$partnerId = null;
		$settings = Zend_Registry::get('config')->settings;
		if(isset($settings->partnerId))
			$partnerId = $settings->partnerId;
		
		$client = Infra_ClientHelper::getClient();
		$client->setVs(null);
		$config = $client->getConfig();
		$config->requestHeaders[] = $this->constructXRemoteAddrHeader($_SERVER['REMOTE_ADDR'], time(), 'admin_console', $settings->remoteAddrHeaderSalt);
		$client->setConfig($config);
		
		try
		{
			if ($this->partnerId)
			{
			    $vs = $client->user->loginByLoginId($this->username, $this->password, $this->partnerId, null, $this->privileges, $this->otp);
	    		$client->setVs($vs);
	    		$user = $client->user->getByLoginId($this->username, $this->partnerId);
	    		$identity = $this->getUserIdentity($user, $vs, $this->partnerId);
	    		return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity);
			}
			
		    if (!$this->vs)
    		    $this->vs = $client->user->loginByLoginId($this->username, $this->password, $partnerId, null, $this->privileges, $this->otp);
    		$client->setVs($this->vs);
    		$user = $client->user->getByLoginId($this->username, $partnerId);
    		$identity = $this->getUserIdentity($user, $this->vs, $user->partnerId);
			
			if ($partnerId && $user->partnerId != $partnerId) {
				return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, null);
			}
			
			return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity);
		}
		catch(Exception $ex)
		{
			if ($ex->getCode() === self::SYSTEM_USER_INVALID_CREDENTIALS || $ex->getCode() === self::SYSTEM_USER_DISABLED || $ex->getCode() === self::USER_WRONG_PASSWORD || $ex->getCode() === self::USER_NOT_FOUND)
				return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, null);
			else
				throw $ex;
		}
	}

	protected function constructXRemoteAddrHeader ($remoteIp, $time, $uniqueId, $salt)
	{
		return self::X_VIDIUN_REMOTE_ADDR . ":$remoteIp,$time,$uniqueId," . md5("$remoteIp,$time,$uniqueId,$salt");	
	}

}