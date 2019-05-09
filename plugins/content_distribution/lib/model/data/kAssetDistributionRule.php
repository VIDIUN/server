<?php
/**
 * @package plugins.contentDistribution
 * @subpackage model.data
 */
class vAssetDistributionRule
{
	/**
	 * @var string
	 */
	private $validationError;

	/**
	 * @var array<vAssetDistributionCondition>
	 */
	private $assetDistributionConditions;
	
	/**
	 * @param asset $asset
	 * @return boolean
	 */
	public function fulfilled(asset $asset)
	{	
		foreach ($this->assetDistributionConditions as $distributionCondition)
		{
			/* @var $distributionCondition vAssetDistributionCondition */
			if (!$distributionCondition->fulfilled($asset))
			{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * @param array<vAssetDistributionCondition> $conditions
	 */
	public function setAssetDistributionConditions(array $conditions)
	{
		$this->assetDistributionConditions = $conditions;
	}
	
	/**
	 * @return array<vAssetDistributionCondition>
	 */
	public function getAssetDistributionConditions()
	{
		return $this->assetDistributionConditions;
	}

	/**
	 * @param string $validationError
	 */
	public function setValidationError($validationError)
	{
		$this->validationError = $validationError;
	}

	/**
	 * @return string
	 */
	public function getValidationError()
	{
		return $this->validationError;
	}
}
		