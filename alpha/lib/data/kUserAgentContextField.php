<?php

/**
 * Returns the current request user agent 
 * @package Core
 * @subpackage model.data
 */
class vUserAgentContextField extends vStringField
{
	/* (non-PHPdoc)
	 * @see vStringField::getFieldValue()
	 */
	protected function getFieldValue(vScope $scope = null) 
	{
		vApiCache::addExtraField(vApiCache::ECF_USER_AGENT);

		if(!$scope)
			$scope = new vScope();
			
		return $scope->getUserAgent();
	}

	/* (non-PHPdoc)
	 * @see vStringValue::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		return false;
	}
}