<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vGeoDistanceCondition extends vMatchCondition
{
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::GEO_DISTANCE);
		parent::__construct($not);
	}
	
	/**
	 * The ip geo coder engine to be used
	 * 
	 * @var int of enum geoCoderType
	 * TODO take the default from vConf for on-prem
	 */
	protected $geoCoderType = geoCoderType::VIDIUN;
	
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
	 * @see vCondition::getFieldValue()
	 */
	public function getFieldValue(vScope $scope)
	{
		vApiCache::addExtraField(array("type" => vApiCache::ECF_COORDINATES,
			vApiCache::ECFD_GEO_CODER_TYPE => $this->getGeoCoderType()),
			vApiCache::COND_GEO_DISTANCE, $this->getStringValues($scope));

		$ip = $scope->getIp();
		$ipGeo = vGeoCoderManager::getGeoCoder($this->getGeoCoderType());
		return array($ipGeo->getCoordinates($ip)); // wrap in an array since otherwise the coordinates will be perceived as a list of two values
	}
	
	/* (non-PHPdoc)
	 * @see vMatchCondition::matches()
	 */
	protected function matches($field, $value)
	{
		return vGeoUtils::isInGeoDistance($field, $value);
	}

	/* (non-PHPdoc)
	 * @see vMatchCondition::shouldFieldDisableCache()
	 */
	public function shouldFieldDisableCache($scope)
	{
		return false;
	}
}
