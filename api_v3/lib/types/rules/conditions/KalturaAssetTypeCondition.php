<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunAssetTypeCondition extends VidiunCondition
{
	/**
	 * @dynamicType VidiunAssetType
	 * @var string
	 */
	public $assetTypes;

	/**
	 * Init object type
	 */
	public function __construct() 
	{
		$this->type = ConditionType::ASSET_TYPE;
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vAssetTypeCondition();

		/** @var $dbObject vAssetTypeCondition */
		$dbObject = parent::toObject($dbObject, $skip);

		if (!is_null($this->assetTypes))
			$dbObject->setAssetTypes(explode(',', $this->assetTypes));

		return $dbObject;
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::fromObject()
	 */
	public function doFromObject($dbObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/** @var $dbObject vAssetTypeCondition */
		parent::doFromObject($dbObject, $responseProfile);
		if($this->shouldGet('AssetTypes', $responseProfile))
			$this->assetTypes = implode(',', $dbObject->getAssetTypes());
	}
}
