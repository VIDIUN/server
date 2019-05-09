<?php
/**
 * @package Admin
 * @subpackage Errors
 */
class Vidiun_AdminException extends Infra_Exception
{
	const VIDIUN_HEADER_ERROR_CODE = 'X-Vidiun-ErrorCode';


	const ERROR_CODE_NO_IDENTITY = 'NO_IDENTITY';
	const ERROR_CODE_PAGE_NOT_FOUND = 'PAGE_NOT_FOUND';

	public function getPrefix()
	{
		return 'Admin';
	}

	public static function getErrorCode(Exception $e)
	{
		if($e instanceof Vidiun_AdminException)
			return $e->getPrefix() . ':' . $e->getCode();

		if($e instanceof Vidiun_Client_Exception)
			return 'Server:' . $e->getCode();

		if($e instanceof Vidiun_Client_ClientException)
			return 'API:' . $e->getCode();

		if($e instanceof Infra_Exception)
			return 'UI-Infra:' . $e->getCode();

		if($e instanceof Zend_Exception)
			return 'Zend:' . $e->getCode();

		return 'Runtime:' . $e->getCode();
	}
}
