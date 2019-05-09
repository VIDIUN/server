<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAssetPropertiesCompareCondition extends VidiunCondition
{
	/**
	 * Array of key/value objects that holds the property and the value to find and compare on an asset object
	 *
	 * @var VidiunKeyValueArray
	 */
	public $properties;

	private static $mapBetweenObjects = array
	(
		'properties',
	);

	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = ConditionType::ASSET_PROPERTIES_COMPARE;
	}

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vAssetPropertiesCompareCondition();

		$dbObject = parent::toObject($dbObject, $skip);

		if (!is_null($this->properties))
		{
			$properties = array();
			foreach($this->properties as $keyValue)
				$properties[$keyValue->key] = $keyValue->value;
			$dbObject->setProperties($properties);
		}

		return $dbObject;
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/** @var $dbObject vAssetPropertiesCompareCondition */
		parent::doFromObject($dbObject, $responseProfile);
		
		if($this->shouldGet('properties', $responseProfile))
			$this->properties = VidiunKeyValueArray::fromKeyValueArray($dbObject->getProperties());
	}
}
