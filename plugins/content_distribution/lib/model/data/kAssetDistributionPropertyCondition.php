<?php
/**
 * @package plugins.contentDistribution
 * @subpackage model.data
 */
class vAssetDistributionPropertyCondition extends vAssetDistributionCondition
{
	private $propertyName;
	private $propertyValue;
	
	/* (non-PHPdoc)
	 * @see vAssetDistributionCondition::fulfilled()
	 */
	public function fulfilled(asset $asset)
	{
		$propName =  $this->propertyName;
		$propValue = $this->propertyValue;
		
		$customPropGetterCallback = array($asset, "get".$propName);
		
		if (!is_callable($customPropGetterCallback)) 
		{
			VidiunLog::info("asset (id = {$asset->getId()}) required property not found: Prop Name = $propName, Prop Value = $propValue");
			return false;
		}			
		
		if ($propValue != call_user_func($customPropGetterCallback))
		{		
			VidiunLog::info("asset (id = {$asset->getId()}) does not match distribution property condition: Prop Name = $propName, Prop Value = $propValue");
			return false;	
		}
		
		VidiunLog::info("asset (id = {$asset->getId()}) MATCHES distribution property condition: Prop Name = $propName, Prop Value = $propValue");
			
		return true; 
	}
	
	/**
	 * @param string $propertyName
	 */
	public function setPropertyName($propertyName = null)
	{
		$this->propertyName = $propertyName;
	}
	
	/**
	 * @return string
	 */
	public function getPropertyName()
	{
		return $this->propertyName;
	}
	
	/**
	 * @param string $propertyValue
	 */
	public function setPropertyValue($propertyValue = null)
	{
		$this->propertyValue = $propertyValue;
	}

	/**
	 * @return string
	 */
	public function getPropertyValue()
	{
		return $this->propertyValue;
	}
}
