<?php
/**
 * @package plugins.captionSearch
 * @subpackage api.objects
 */
class VidiunCaptionAssetItem extends VidiunObject
{
	/**
	 * The Caption Asset object
	 * 
	 * @var VidiunCaptionAsset
	 */
	public $asset;
	
	/**
	 * The entry object
	 * 
	 * @var VidiunBaseEntry
	 */
	public $entry;
	
	/**
	 * @var int
	 */
	public $startTime;
	
	/**
	 * @var int
	 */
	public $endTime;
	
	/**
	 * @var string
	 */
	public $content;
	
	private static $map_between_objects = array
	(
		"startTime",
		"endTime",
		"content",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		/* @var $source_object CaptionAssetItem */
		
		$ret = parent::doFromObject($source_object, $responseProfile);
		
		if($this->shouldGet('asset', $responseProfile))
		{
			$this->asset = new VidiunCaptionAsset();
			$this->asset->fromObject($source_object->getAsset());
		}
		
		if($this->shouldGet('entry', $responseProfile))
		{
			$entry = $source_object->getEntry();
			if ($entry)
			{
				$this->entry = VidiunEntryFactory::getInstanceByType($entry->getType());
				$this->entry->fromObject($entry);
			}
		}
			
		return $ret;
	}
}