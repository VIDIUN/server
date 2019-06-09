<?php
/**
 * @package plugins.caption
 * @subpackage api.objects
 * @relatedService CaptionAssetService
 */
class VidiunCaptionAsset extends VidiunAsset
{
	/**
	 * The Caption Params used to create this Caption Asset
	 * 
	 * @var int
	 * @insertonly
	 * @filter eq,in
	 */
	public $captionParamsId;
	
	/**
	 * The language of the caption asset content
	 * 
	 * @var VidiunLanguage
	 */
	public $language;
	
	/**
	 * The language of the caption asset content
	 * 
	 * @var VidiunLanguageCode
	 * @readonly
	 */
	public $languageCode;
	
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
	 * The status of the asset
	 * 
	 * @var VidiunCaptionAssetStatus
	 * @readonly 
	 * @filter eq,in,notin
	 */
	public $status;

	/**
	 * The parent id of the asset
	 * @var string
	 * @insertonly
	 *
	 */
	public $parentId;

	/**
	 * The Accuracy of the caption content
	 * @var int 
	 */
	public $accuracy;
	
	/**
	 * The Accuracy of the caption content
	 * @var bool
	 */
	public $displayOnPlayer;

	private static $map_between_objects = array
	(
		"captionParamsId" => "flavorParamsId",
		"language",
		"isDefault" => "default",
		"label",
		"format" => "containerFormat",
		"status",
		"parentId",
		"accuracy",
		"displayOnPlayer",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$ret = parent::doFromObject($source_object, $responseProfile);
				
		if($this->shouldGet('languageCode', $responseProfile))
		{
			$languageReflector = VidiunTypeReflectorCacher::get('VidiunLanguage');
			$languageCodeReflector = VidiunTypeReflectorCacher::get('VidiunLanguageCode');
			if($languageReflector && $languageCodeReflector)
			{
				$languageCode = $languageReflector->getConstantName($this->language);
				if($languageCode)
					$this->languageCode = $languageCodeReflector->getConstantValue($languageCode);
			}
		}
			
		return $ret;
	}

	public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if (!is_null($this->captionParamsId))
		{
			$dbAssetParams = assetParamsPeer::retrieveByPK($this->captionParamsId);
			if ($dbAssetParams)
			{
				$object_to_fill->setFromAssetParams($dbAssetParams);
			}
		}
		
		if ($this->format === null &&
			$object_to_fill->getContainerFormat() === null)		// not already set by setFromAssetParams
		{
			$this->format = VidiunCaptionType::SRT;
		}
		
		return parent::toInsertableObject ($object_to_fill, $props_to_skip);
	}


	/* (non-PHPdoc)
	 * @see VidiunObject::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		parent::validateForInsert($propertiesToSkip);
	}
}
