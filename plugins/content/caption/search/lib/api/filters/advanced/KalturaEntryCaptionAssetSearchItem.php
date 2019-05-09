<?php
/**
 * @package plugins.captionSearch
 * @subpackage api.filters
 */
class VidiunEntryCaptionAssetSearchItem extends VidiunSearchItem
{
	/**
	 * @var string
	 */
	public $contentLike;

	/**
	 * @var string
	 */
	public $contentMultiLikeOr;

	/**
	 * @var string
	 */
	public $contentMultiLikeAnd;

	private static $map_between_objects = array
	(
		"contentLike",
		"contentMultiLikeOr",
		"contentMultiLikeAnd"
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(!$object_to_fill)
			$object_to_fill = new EntryCaptionAssetSearchFilter();
			
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}
