<?php
/**
 * @package Admin
 * @subpackage Authentication
 */
class Vidiun_AdminAuthAdapter extends Infra_AuthAdapter
{
	/* (non-PHPdoc)
	 * @see Infra_AuthAdapter::getUserIdentity()
	 */
	protected function getUserIdentity(Vidiun_Client_Type_User $user=null, $vs=null, $partnerId=null)
	{
		return new Vidiun_AdminUserIdentity($user, $vs, $this->timezoneOffset, $partnerId);
	}
}
