<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vAssetTypeCondition extends vCondition
{
	/**
	 * @var array
	 */
	private $assetTypes;
	
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::ASSET_TYPE);
		parent::__construct($not);
	}

	/**
	 * @param vScope $scope
	 * @return bool
	 */
	protected function internalFulfilled(vScope $scope)
	{
		//get flavor from scope
		$asset = ($scope instanceof  accessControlScope) ? $scope->getAsset() : null;
		if ($asset)
			return in_array($asset->getType(), $this->assetTypes);
		return false;
	}

	/**
	 * @param array $assetTypes
	 */
	public function setAssetTypes($assetTypes)
	{
		$this->assetTypes = $assetTypes;
	}

	/**
	 * @return array
	 */
	public function getAssetTypes()
	{
		return $this->assetTypes;
	}

}
