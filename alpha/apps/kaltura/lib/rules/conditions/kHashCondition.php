<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vHashCondition extends vCondition
{
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::HASH);
		parent::__construct($not);
	}
	
	/**
	 * @var string
	 */
	protected $hashName;

	/**
	 * @var string
	 */
	protected $hashSecret;

	/**
	 * @param string $hashName
	 */
	public function setHashName($hashName)
	{
		$this->hashName = $hashName;
	}

	/**
	 * @return string
	 */
	public function getHashName()
	{
		return $this->hashName;
	}

	/**
	 * @param string $hashSecret
	 */
	public function setHashSecret($hashSecret)
	{
		$this->hashSecret = $hashSecret;
	}

	/**
	 * @return string
	 */
	public function gethashSecret()
	{
		return $this->hashSecret;
	}

	/* (non-PHPdoc)
	 * @see vCondition::internalFulfilled()
	 */
	protected function internalFulfilled(vScope $scope)
	{
		$hashes = $scope->getHashes();
		if (is_array($hashes) && isset($hashes[$this->hashName]))
		{
			$sentHash = $hashes[$this->hashName];
			$compareHash = md5($this->hashSecret. vCurrentContext::$vs);
			if ($sentHash === $compareHash)
			{
				VidiunLog::info("Correct hash sent");
				return false;
			}
			
		}
		
		VidiunLog::info("Incorrect hash sent");
		return true;
    }

	/* (non-PHPdoc)
	 * @see vCondition::shouldDisableCache()
	 */
	public function shouldDisableCache($scope)
	{
		return false;
	}
}
