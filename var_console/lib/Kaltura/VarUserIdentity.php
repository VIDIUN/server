<?php
/**
 * @package Var
 * @subpackage Authentication
 */
class Vidiun_VarUserIdentity extends Infra_UserIdentity
{
	/**
	 * @var string
	 */
	protected $password;
	
	/**
	 * @return string $password
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password)
	{
		$this->password = $password;
	}
}