<?php
/**
 * @package plugins.aptionAssetItemCuePoint
 * @subpackage api.objects
 */
class KalturaCaptionAssetItemCuePoint extends KalturaCuePoint
{
	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $content;
	
	/**
	 * @var string
	 * @filter eq, in
	 */
	public $captionAssetId;
	
	/**
	 * @var int 
	 * @filter gte,lte,order
	 */
	public $endTime;

	public function __construct()
	{
		$this->cuePointType = CaptionAssetItemCuePointPlugin::getApiValue(CaptionAssetItemCuePointType::CAPTION_ASSET_ITEM);
	}
	
	private static $map_between_objects = array
	(
		"endTime",
		"captionAssetId",
		"content" => "name",
	);
	
	/* (non-PHPdoc)
	 * @see KalturaCuePoint::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see KalturaObject::toInsertableObject()
	 */
	public function toInsertableObject($object_to_fill = null, $props_to_skip = array())
	{
		if(is_null($object_to_fill))
			$object_to_fill = new CaptionAssetItemCuePoint();
			
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}
	
	/* (non-PHPdoc)
	 * @see KalturaCuePoint::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		parent::validateForInsert($propertiesToSkip);
	}
}