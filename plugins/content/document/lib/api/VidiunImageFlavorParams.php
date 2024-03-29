<?php
/**
 * @package plugins.document
 * @subpackage api.objects
 */
class VidiunImageFlavorParams extends VidiunFlavorParams 
{
	public function toObject($object = null, $skip = array())
	{
		if(is_null($object))
			$object = new ImageFlavorParams();
		
		parent::toObject($object, $skip);
		$object->setType(DocumentPlugin::getAssetTypeCoreValue(DocumentAssetType::IMAGE));
		return $object;
	}
	
	/**
	 * @var int
	 */
	public $densityWidth;
	
	/**
	 * @var int
	 */
	public $densityHeight;
	
	/**
	 * @var int
	 */
	public $sizeWidth;
	
	/**
	 * @var int
	 */
	public $sizeHeight;
	
	/**
	 * @var int
	 */
	public $depth;
	
	
	private static $map_between_objects = array
	(
		'densityWidth',
		'densityHeight',
		'sizeWidth',
		'sizeHeight',
		'depth',
	);
	
	// attributes that defined in flavorParams and not in PdfFlavorParams
	private static $skip_attributes = array
	(
		"videoConstantBitrate",
		"videoBitrateTolerance",
		"videoCodec",
		"videoBitrate",
		"audioCodec",
		"audioBitrate",
		"audioChannels",
		"audioSampleRate",
		"frameRate",
		"aspectRatioProcessingMode",
		"clipOffset",
		"clipDuration",
		"isGopInSec",
		"gopSize"
	);
	
	public function getMapBetweenObjects()
	{
		$map = array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
		foreach(self::$skip_attributes as $skip_attribute)
		{
			if(isset($map[$skip_attribute]))
				unset($map[$skip_attribute]);
				
			$key = array_search($skip_attribute, $map);
			if($key !== false)
				unset($map[$key]);
		}
		return $map;
	}
}