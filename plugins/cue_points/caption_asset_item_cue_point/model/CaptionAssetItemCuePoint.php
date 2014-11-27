<?php


/**
 * @package plugins.captionAssetItemCuePoint
 * @subpackage model
 */
class CaptionAssetItemCuePoint extends CuePoint
{
	/**
	 * @var CaptionAsset
	 */
	protected $aAsset = null;
	
	/**
	 * @var entry
	 */
	protected $aEntry = null;
	
	const CUSTOM_DATA_FIELD_CAPTION_ASSET_ID = 'captionAssetId';
	
	public function __construct() 
	{
		parent::__construct();
		$this->applyDefaultValues();
	}

	/**
	 * Applies default values to this object.
	 * This method should be called from the object's constructor (or equivalent initialization method).
	 * @see __construct()
	 */
	public function applyDefaultValues()
	{
		$this->setType(CaptionAssetItemCuePointPlugin::getCuePointTypeCoreValue(CaptionAssetItemCuePointType::CAPTION_ASSET_ITEM));
	}
	
	public function getAsset()
	{
		if(!$this->aAsset && $this->getCaptionAssetId())
			$this->aAsset = assetPeer::retrieveById($this->getCaptionAssetId());
		
		return $this->aAsset;
	}
	
	/**
	 * @return string
	 */
	public function getPartnerDescription()
	{
		return $this->getAsset()->getPartnerDescription();
	}
	
	/**
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->getAsset()->getLanguage();
	}
	
	/**
	 * @return string
	 */
	public function getFormat()
	{
		return $this->getAsset()->getContainerFormat();
	}
	
	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->getAsset()->getLabel();
	}
	
	/**
	 * @return int
	 */
	public function getCaptionAssetStatus()
	{
		return $this->getAsset()->getStatus();
	}
	
	/**
	 * @return int
	 */
	public function getSize()
	{
		return $this->getAsset()->getSize();
	}
	
	public function setCaptionAssetId($v)		{return $this->putInCustomData(self::CUSTOM_DATA_FIELD_CAPTION_ASSET_ID, (string)$v);}
	public function getCaptionAssetId()		{return $this->getFromCustomData(self::CUSTOM_DATA_FIELD_CAPTION_ASSET_ID);}
}