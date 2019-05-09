<?php
/**
 * @package Var
 * @subpackage Authentication
 */
class Vidiun_VarAuthAdapter extends Infra_AuthAdapter
{
	/* (non-PHPdoc)
	 * @see Infra_AuthAdapter::getUserIdentity()
	 */
	protected function getUserIdentity(Vidiun_Client_Type_User $user = null, $vs = null, $partnerId = null)
	{
		$identity = new Vidiun_VarUserIdentity($user, $vs, $this->timezoneOffset, $partnerId);
		$identity->setPassword($this->password);
		
		return $identity;
	}
	
	/* (non-PHPdoc)
	 * @see Infra_AuthAdapter::authenticate()
	 */
	public function authenticate()
	{
		$result = parent::authenticate();
		if($result->getCode() != Zend_Auth_Result::SUCCESS)
			return $result;
			
		$identity = $result->getIdentity();
		if(!($identity instanceof Vidiun_VarUserIdentity))
			return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_UNCATEGORIZED, null, array('Identity is not a multi-publisher identity'));
			
		$client = Infra_ClientHelper::getClient();
		$client->setVs($identity->getVs());
		
		$settings = Zend_Registry::get('config')->settings;
		
		try
		{
    		if (isset($settings->requiredPermissions) && $settings->requiredPermissions)
    		{
    		    $requiredPermissionsArr = explode(",", $settings->requiredPermissions);
    		    
    		    $hasRequiredPermissions = true;
    		    foreach ($requiredPermissionsArr as $requiredPermission)
			    {
			        $permissionFilter = new Vidiun_Client_Type_PermissionFilter();
			        $permissionFilter->nameEqual = $requiredPermission;
			        $permissionFilter->statusEqual = Vidiun_Client_Enum_PermissionStatus::ACTIVE;
			        $permissions = $client->permission->listAction($permissionFilter, new Vidiun_Client_Type_FilterPager());
			        if (!$permissions->totalCount)
			        {
			            $hasRequiredPermissions = false;
			            break;
			        }
			    }
		    
			    if (!$hasRequiredPermissions)
			    {
			        $filter = new Vidiun_Client_VarConsole_Type_VarConsolePartnerFilter();
    		        $filter->partnerPermissionsExist = $settings->requiredPermissions;
    		        $filter->groupTypeIn = Vidiun_Client_Enum_PartnerGroupType::GROUP . "," . Vidiun_Client_Enum_PartnerGroupType::VAR_GROUP;
        		    
        			$userPartners = $client->partner->listPartnersForUser($filter);
        			
        			if (!$userPartners->totalCount)
        			    return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, null);
        			
        			$authorizedPartnerId = $userPartners->objects[0]->id;
        			
        			$client->setVs(null);
        		    $vs = $client->user->loginByLoginId($this->username, $this->password, $authorizedPartnerId);
        			$client->setVs($vs);
        			$user = $client->user->getByLoginId($this->username, $authorizedPartnerId);
        			$identity = $this->getUserIdentity($user, $vs, $authorizedPartnerId);
			    }
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

}