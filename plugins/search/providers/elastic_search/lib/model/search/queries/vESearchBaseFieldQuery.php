<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.search
 */
abstract class vESearchBaseFieldQuery extends vESearchBaseQuery
{
	const BOOST_KEY = 'boost';
	
	/**
	 * @var string
	 */
	protected $fieldName;
	
	/**
	 * @var string
	 */
	protected $boostFactor;

	/**
	 * @return string
	 */
	public function getFieldName()
	{
		return $this->fieldName;
	}
	
	/**
	 * @param string $fieldName
	 */
	public function setFieldName($fieldName)
	{
		$this->fieldName = $fieldName;
	}
	
	/**
	 * @return string
	 */
	public function getBoostFactor()
	{
		return $this->boostFactor;
	}
	
	/**
	 * @param string $boostFactor
	 */
	public function setBoostFactor($boostFactor)
	{
		$this->boostFactor = $boostFactor;
	}

	/**
	 * @return boolean
	 */
	public function getShouldMoveToFilterContext()
	{
		if($this->getBoostFactor() == vESearchQueryManager::DEFAULT_BOOST_FACTOR)
			return true;
		return false;
	}

}
