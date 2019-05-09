<?php

/**
 * Returns the current request IP address context 
 * @package Core
 * @subpackage model.data
 */
class vIpAddressContextField extends vStringField
{
	/* (non-PHPdoc)
	 * @see vIntegerField::getFieldValue()
	 */
	protected function getFieldValue(vScope $scope = null)
	{
		vApiCache::addExtraField(vApiCache::ECF_IP);

		if(!$scope)
			$scope = new vScope();

		return $scope->getIp();
	}

	/* (non-PHPdoc)
	 * @see vStringValue::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		return false;
	}
}