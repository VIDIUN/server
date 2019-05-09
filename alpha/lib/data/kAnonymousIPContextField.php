<?php

/**
 * Return the current request coodinates context as calculated based on the IP address 
 * @package Core
 * @subpackage model.data
 */
class vAnonymousIPContextField extends vStringField
{
	/**
	 * The ip geo coder engine to be used
	 * 
	 * @var int of enum geoCoderType
	 */
	protected $geoCoderType = geoCoderType::VIDIUN;
	
	/* (non-PHPdoc)
	 * @see vIntegerField::getFieldValue()
	 */
	protected function getFieldValue(vScope $scope = null)
	{
		vApiCache::addExtraField(vApiCache::ECF_ANONYMOUS_IP);

		if(!$scope)
			$scope = new vScope();
			
		$ip = $scope->getIp();
		$ipGeo = vGeoCoderManager::getGeoCoder($this->getGeoCoderType());
		$anonymousIPInfo = $ipGeo->getAnonymousInfo($ip);
		return $anonymousIPInfo;
	}
	
	/**
	 * @param int $geoCoderType of enum geoCoderType
	 */
	public function setGeoCoderType($geoCoderType)
	{
		$this->geoCoderType = $geoCoderType;
	}
	
	/**
	 * @return array
	 */
	function getGeoCoderType()
	{
		return $this->geoCoderType;
	}

	/* (non-PHPdoc)
	 * @see vStringValue::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		return false;
	}
}