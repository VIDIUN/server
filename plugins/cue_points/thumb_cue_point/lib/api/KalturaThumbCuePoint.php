<?php
/**
 * @package plugins.thumbCuePoint
 * @subpackage api.objects
 */
class VidiunThumbCuePoint extends VidiunCuePoint
{
	const MAX_TITLE_LEN = 255;

	/**
	 * @var string
	 */
	public $assetId;
	
	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $description;
	
	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $title;
	
	/**
	 * The sub type of the ThumbCuePoint
	 * 
	 * @var VidiunThumbCuePointSubType
	 * @filter eq,in
	 */
	public $subType;

	public function __construct()
	{
		$this->cuePointType = ThumbCuePointPlugin::getApiValue(ThumbCuePointType::THUMB);
	}
	
	private static $map_between_objects = array
	(
		"assetId",
		"title" => "name",
		"description" => "text",
		"subType",
	);
	
	/* (non-PHPdoc)
	 * @see VidiunCuePoint::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toInsertableObject()
	 */
	public function toInsertableObject($object_to_fill = null, $props_to_skip = array())
	{
		if(is_null($object_to_fill))
			$object_to_fill = new ThumbCuePoint();

		if(strlen ($this->title) > self::MAX_TITLE_LEN)
			$this->title = vString::alignUtf8String($this->title, self::MAX_TITLE_LEN);

		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}


	
	/* (non-PHPdoc)
	 * @see VidiunCuePoint::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{	
		if($this->assetId !== null)	
			$this->validateTimedThumbAssetId();
		
		parent::validateForInsert($propertiesToSkip);
	}
	
	public function validateTimedThumbAssetId()
	{
		$timedThumb = assetPeer::retrieveById($this->assetId);
		
		if(!$timedThumb)
			throw new VidiunAPIException(VidiunErrors::ASSET_ID_NOT_FOUND, $this->assetId);
		
		if($timedThumb->getType() != ThumbCuePointPlugin::getAssetTypeCoreValue(timedThumbAssetType::TIMED_THUMB_ASSET))
			throw new VidiunAPIException(VidiunErrors::THUMB_ASSET_ID_IS_NOT_TIMED_THUMB_TYPE, $this->assetId);
	}
}
