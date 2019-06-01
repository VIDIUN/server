<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vScope
{
	/**
	 * @var string
	 */
	protected $referrer;
	
	/**
	 * @var string
	 */
	protected $ip;
	
	/**
	 * @var vs
	 */
	protected $vs;
	
	/**
	 * @var string
	 */
	protected $userAgent;
	
	/**
	 * Unix timestamp (In seconds) to be used to test entry scheduling, keep null to use now.
	 * @var int
	 */
	protected $time;
	
	/**
	 * Unix timestamp (In seconds) to be used to test entry scheduling, keep null to use now.
	 * @var array<vValue>
	 */
	protected $dynamicValues = array();
	
	/**
	 * @var string
	 */
	protected $entryId;
	
	/**
	 * Indicates what contexts should be tested 
	 * No contexts means any context
	 * 
	 * @var array of ContextType
	 */
	protected $contexts = array();
	
	
	public function __construct()
	{
		$this->setIp(requestUtils::getRemoteAddress());
		$this->setReferrer(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : null);
		$this->setUserAgent(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);	
		$this->setVs(vCurrentContext::$vs_object ? vCurrentContext::$vs_object : null);
	}
	
	/**
	 * @param string $v
	 */
	public function setReferrer($v)
	{
		$this->referrer = $v;
	}
	
	/**
	 * @param string $v
	 */
	public function setIp($v)
	{
		$this->ip = $v;
	}
	
	/**
	 * @param vs $v
	 */
	public function setVs($v)
	{
		if(is_string($v))
			$v = vs::fromSecureString($v);
			
		$this->vs = $v;
	}
	
	/**
	 * @param string $userAgent
	 */
	public function setUserAgent($userAgent) 
	{
		$this->userAgent = $userAgent;
	}

	/**
	 * @return string
	 */
	public function getReferrer()
	{
		return $this->referrer;
	}
	
	/**
	 * @return string
	 */
	public function getIp()
	{
		return $this->ip;
	}
	
	/**
	 * @return vs
	 */
	public function getVs()
	{
		return $this->vs;
	}
	
	/**
	 * @return string the $userAgent
	 */
	public function getUserAgent() 
	{
		return $this->userAgent;
	}

	/**
	 * @return int $time
	 */
	public function getTime() 
	{
		if(is_null($this->time))
		{
			return vApiCache::getTime();
		}
			
		return $this->time;
	}

	/**
	 * @param int $time
	 */
	public function setTime($time) 
	{
		$this->time = $time;
	}

	public function resetDynamicValues() 
	{
		$this->dynamicValues= array();
	}

	/**
	 * @param string $key
	 * @param vValue $value
	 */
	public function addDynamicValue($key, vValue $value) 
	{
		$this->dynamicValues[$key] = $value;
	}

	/**
	 * @param string $key
	 * @return bool wasRemoved
	 */
	public function removeDynamicValue($key)
	{
		if ($this->dynamicValues[$key])
		{
			unset($this->dynamicValues[$key]);
			return true;
		}
		return false;
	}

	/**
	 * Get dynamic field values as an associative array: key => value
	 * Returns empty string if dynamic value with the given key is missing
	 *
	 * @param string $keyPrefix
	 * @param string $keySuffix
	 * @return array
	 */
	public function getDynamicValues($keyPrefix = '', $keySuffix = '')
	{
		$values = array();
		foreach($this->dynamicValues as $key => $value)
		{
			$values[$keyPrefix . $key . $keySuffix] = $this->calculateDynamicValue($value);
		}
		
		return $values;
	}

	/**
	 * Get dynamic field value by key name
	 *
	 * @param string $key Field key name, without prefix and suffix
	 * @return string
	 */
	public function getDynamicValueByKey($key)
	{
		if(isset($this->dynamicValues[$key]))
		{
			return $this->calculateDynamicValue($this->dynamicValues[$key]);
		}
		else
		{
			return '';
		}
	}

	/**
	 * Calculate dynamic field value by vValue object
	 *
	 * @param vValue $value
	 * @return string
	 */
	protected function calculateDynamicValue($value)
	{
		if($value instanceof IScopeField)
			$value->setScope($this);

		$dynamicValue = $value->getValue();
		if(is_null($dynamicValue))
			$dynamicValue = '';

		return $dynamicValue;
	}

	/**
	 * @param string $v
	 */
	public function setEntryId($v)
	{
		$this->entryId = $v;
	}
	
	/**
	 * @param array $contexts array of ContextType
	 */
	public function setContexts(array $contexts) 
	{
		$this->contexts = $contexts;
	}
	
	/**
	 * @return string
	 */
	public function getEntryId()
	{
		return $this->entryId;
	}	
	
	/**
	 * @return array of ContextType
	 */
	public function getContexts() 
	{
		return $this->contexts;
	}

	/**
	 * @param int $context enum of ContextType
	 * @return bool
	 */
	public function isInContext($context)
	{
		if(!is_array($this->contexts) || !count($this->contexts))
			return true;
			
		return in_array($context, $this->contexts);
	}
}