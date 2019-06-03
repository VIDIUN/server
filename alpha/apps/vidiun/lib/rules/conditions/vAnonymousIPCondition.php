<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vAnonymousIPCondition extends vMatchCondition
{
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::ANONYMOUS_IP);
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
		vApiCache::addExtraField(array("type" => vApiCache::ECF_ANONYMOUS_IP,
			vApiCache::ECFD_GEO_CODER_TYPE => $this->getGeoCoderType()),
			$this->getMatchType() == MatchConditionType::MATCH_ALL ? vApiCache::COND_MATCH_ALL : vApiCache::COND_MATCH, $this->getStringValues($scope));
		
		$ip = $scope->getIp();
		$ipGeo = vGeoCoderManager::getGeoCoder($this->getGeoCoderType());
		return $ipGeo->getAnonymousInfo($ip);
	}
	
	/* (non-PHPdoc)
	 * @see vMatchCondition::matches()
	 */
	protected function matches($field, $value)
	{
		return parent::matches(trim(strtolower($field), " \n\r\t"), trim(strtolower($value), " \n\r\t"));		
	}

	/* (non-PHPdoc)
	 * @see vMatchCondition::shouldFieldDisableCache()
	 */
	public function shouldFieldDisableCache($scope)
	{
		return false;
	}
}
