<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vIpAddressCondition extends vMatchCondition
{
	const PARTNER_INTERNAL_IP = 'partnerInternalIp';

	public function __construct($not = false)
	{
		$this->setType(ConditionType::IP_ADDRESS);
		parent::__construct($not);
	}
	
	/**
	 * @var bool
	 */
	protected $acceptInternalIps;
	
	/**
	 * @var string
	 */
	protected $httpHeader;
	
	/**
	 * @param bool $acceptInternalIps
	 */
	public function setAcceptInternalIps($acceptInternalIps)
	{
	    $this->acceptInternalIps = $acceptInternalIps;
	}
	
	/**
	 * @return bool
	 */
	public function getAcceptInternalIps()
	{
	    return $this->acceptInternalIps;
	}
	
	/**
	 * @param string $httpHeader
	 */
	public function setHttpHeader($httpHeader)
	{
	    $this->httpHeader = $httpHeader;
	}
	
	/**
	 * @return string
	 */
	public function getHttpHeader()
	{
	    return $this->httpHeader;
	}
	
	/* (non-PHPdoc)
	 * @see vCondition::getFieldValue()
	 */
	public function getFieldValue(vScope $scope)
	{
		if ($this->getHttpHeader() || $this->getAcceptInternalIps())
		{
			vApiCache::addExtraField(array("type" => vApiCache::ECF_IP,
					vApiCache::ECFD_IP_HTTP_HEADER => $this->getHttpHeader(),
					vApiCache::ECFD_IP_ACCEPT_INTERNAL_IPS => $this->getAcceptInternalIps()),
					vApiCache::COND_IP_RANGE, $this->getStringValues($scope));

			$headerIp = infraRequestUtils::getIpFromHttpHeader($this->getHttpHeader(), $this->getAcceptInternalIps(), true);
			if ($headerIp)
			{
				$this->setExtraProperties(self::PARTNER_INTERNAL_IP, $headerIp);
			}
			return $headerIp;
		}
		
		vApiCache::addExtraField(vApiCache::ECF_IP, vApiCache::COND_IP_RANGE, $this->getStringValues($scope));
		return $scope->getIp();
	}

	/* (non-PHPdoc)
	 * @see vMatchCondition::matches()
	 */
	protected function matches($field, $value)
	{
		return vIpAddressUtils::isIpInRanges($field, $value);
	}

	/**
	 * @param vScope $scope
	 * @return bool
	 */
	public function shouldFieldDisableCache($scope)
	{
		return false;
	}
}
