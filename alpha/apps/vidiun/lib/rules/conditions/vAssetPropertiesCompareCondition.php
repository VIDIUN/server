<?php
/**
 * @package Core
 * @subpackage model.data
 */
class vAssetPropertiesCompareCondition extends vCondition
{
	/**
	 * @var array
	 */
	private $properties;
	
	/* (non-PHPdoc)
	 * @see vCondition::__construct()
	 */
	public function __construct($not = false)
	{
		$this->setType(ConditionType::ASSET_PROPERTIES_COMPARE);
		parent::__construct($not);
	}

	/**
	 * @param vScope $scope
	 * @return bool
	 */
	protected function internalFulfilled(vScope $scope)
	{
		// no properties defined, the condition is fulfilled
		if (count($this->getProperties()) == 0)
			return true;
			
		$entryId = $scope->getEntryId();
		$entryAssets = assetPeer::retrieveReadyByEntryId($scope->getEntryId());
		foreach($entryAssets as $asset)
		{
			$assetFulfilled = $this->assetFulfilled($asset);
			if ($assetFulfilled)
				return true;
		}

		return false;
	}

	/**
	 * @param array $properties
	 */
	public function setProperties($properties)
	{
		$this->properties = $properties;
	}

	/**
	 * @return array
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	protected function assetFulfilled(asset $asset)
	{
		VidiunLog::info('Checking asset id '.$asset->getId());
		foreach($this->getProperties() as $propName => $propValue)
		{
			VidiunLog::info('Comparing property ' . $propName .' with value '. $propValue);

			$getterCallback = array($asset, "get".$propName);
			if (!is_callable($getterCallback))
			{
				VidiunLog::info('Property not found on asset');
				return false;
			}

			if ($propValue != call_user_func($getterCallback))
			{
				VidiunLog::info('Property value does not match');
				return false;
			}

			VidiunLog::info('Property value was matched');
		}

		return true;
	}

}
