<?php
/**
 * @package UI-infra
 * @subpackage Authentication
 */
class Infra_UserIdentity
{
	/**
	 * Current user object
	 * @var Vidiun_Client_Type_User
	 */
	protected $user;
	
	/**
	 * Current vidiun session string
	 * @var string
	 */
	protected $vs;
	
	/**
	 * Current user permissions
	 * @var array<string>
	 */
	protected $permissions = null;
	
	/**
	 * @var int 
	 */
	protected $timezoneOffset;
	
	/**
	 * Partner id of the current logged-in partner.
	 * @var int
	 */
	protected $partnerId;
	
	/**
	 * Init a new UserIdentity instance with the given parameters
	 * @param Vidiun_Client_Type_User $user
	 * @param string $vs
	 * @param int $partnerId
	 */
	public function __construct(Vidiun_Client_Type_User $user = null, $vs = null, $timezoneOffset = null, $partnerId = null)
	{
		$this->user = $user;
		$this->vs = $vs;
		$this->timezoneOffset = $timezoneOffset;
		$this->partnerId = $partnerId;
	}
	
	/**
	 * @return Vidiun_Client_Type_User saved user object
	 */
	public function getUser()
	{
		return $this->user;
	}
	
	/**
	 * @return string vs string
	 */
	public function getVs()
	{
		return $this->vs;
	}
	
	public function getPermissions()
	{
		if (is_null($this->permissions)) {
			$this->initPermissions();
		}
		
		return $this->permissions;
	}
	
	private function initPermissions()
	{
		try{
			$client = Infra_ClientHelper::getClient();
			$permissions = $client->permission->getCurrentPermissions();
			$this->permissions = array_map('trim', explode(',', $permissions));
		}
		catch (Exception $e)
		{
			VidiunLog::err($e->getMessage());
			$this->permissions = array(Vidiun_Client_Enum_PermissionName::ALWAYS_ALLOWED_ACTIONS);
		}
	}
	
	/**
     * @return the $timezoneOffset
     */
    public function getTimezoneOffset ()
    {
        return $this->timezoneOffset;
    }
    
	/**
     * @return int $partnerId
     */
    public function getPartnerId ()
    {
        return $this->partnerId;
    }
}