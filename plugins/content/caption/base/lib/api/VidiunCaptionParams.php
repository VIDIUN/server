<?php
/**
 * @package plugins.caption
 * @subpackage api.objects
 */
class VidiunCaptionParams extends VidiunAssetParams 
{
	/**
	 * The language of the caption content
	 * 
	 * @var VidiunLanguage
	 * @insertonly
	 */
	public $language;
	
	/**
	 * Is default caption asset of the entry
	 * 
	 * @var VidiunNullableBoolean
	 */
	public $isDefault;
	
	/**
	 * Friendly label
	 * 
	 * @var string
	 */
	public $label;
	
	/**
	 * The caption format
	 * 
	 * @var VidiunCaptionType
	 * @filter eq,in
	 * @insertonly
	 */
	public $format;
	
	/**
	 * Id of the caption params or the flavor params to be used as source for the caption creation
	 * @var int
	 */
	public $sourceParamsId = 0;
	
	private static $map_between_objects = array
	(
		"language",
		"isDefault",
		"label",
		"format",
		"sourceParamsId",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($object = null, $skip = array())
	{
		if(is_null($object))
			$object = new CaptionParams();
			
		return parent::toObject($object, $skip);
	}
}