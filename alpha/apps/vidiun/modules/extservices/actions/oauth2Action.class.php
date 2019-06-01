<?php

/**
 * @package Core
 * @subpackage externalServices
 */
abstract class oauth2Action extends sfAction{

	const EXPIRY_SECONDS = 1800; // 30 minutes

	private function generateVs($partnerId, $additionalData, $privileges)
	{
		$partner = $this->getPartner($partnerId);
		$limitedVs = '';
		$result = vSessionUtils::startVSession($partnerId, $partner->getAdminSecret(), '', $limitedVs, self::EXPIRY_SECONDS, vSessionBase::SESSION_TYPE_ADMIN, '', $privileges, null, $additionalData);
		if ($result < 0)
			throw new Exception('Failed to create limited session for partner '.$partnerId);

		return $limitedVs;
	}

	protected function generateTimeLimitedVsWithData($partnerId, $stateData)
	{
		$privileges = vSessionBase::PRIVILEGE_ACTIONS_LIMIT.':0';
		$additionalData =  json_encode($stateData);
		return $this->generateVs($partnerId, $additionalData, $privileges);
	}

	protected function generateTimeLimitedVs($partnerId)
	{
		return $this->generateVs($partnerId, null, null);
	}

	protected function getPartner($partnerId)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if (is_null($partner))
			throw new Exception('Partner id '. $partnerId.' not found');

		return $partner;
	}

	protected function processVs($vsStr, $requiredPermission = null)
	{
		try
		{
			vCurrentContext::initVsPartnerUser($vsStr);
		}
		catch(Exception $ex)
		{
			VidiunLog::err($ex);
			return false;
		}

		if (vCurrentContext::$vs_object->type != vs::SESSION_TYPE_ADMIN)
		{
			VidiunLog::err('Vs is not admin');
			return false;
		}

		try
		{
			vPermissionManager::init(vConf::get('enable_cache'));
		}
		catch(Exception $ex)
		{
			if (strpos($ex->getCode(), 'INVALID_ACTIONS_LIMIT') === false) // allow using limited vs
			{
				VidiunLog::err($ex);
				return false;
			}
		}
		if ($requiredPermission)
		{
			if (!vPermissionManager::isPermitted(PermissionName::ADMIN_PUBLISHER_MANAGE))
			{
				VidiunLog::err('Vs is missing "ADMIN_PUBLISHER_MANAGE" permission');
				return false;
			}
		}

		return true;
	}

}