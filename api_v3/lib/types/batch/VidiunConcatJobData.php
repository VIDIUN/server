<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunConcatJobData extends VidiunJobData
{
	/**
	 * Source files to be concatenated
	 * @var VidiunStringArray
	 */
	public $srcFiles;
	
	/**
	 * Output file
	 * @var string
	 */
	public $destFilePath;
	
	/**
	 * Flavor asset to be ingested with the output
	 * @var string
	 */
	public $flavorAssetId;
	
	/**
	 * Clipping offset in seconds
	 * @var float
	 */
	public $offset;
	
	/**
	 * Clipping duration in seconds
	 * @var float
	 */
	public $duration;

	/**
	 * duration of the concated video
	 * @var float
	 */
	public $concatenatedDuration;

	/**
	 * Should Sort the clip parts
	 * @var bool
	 */
	public $shouldSort;

	private static $map_between_objects = array
	(
		'srcFiles',
		'destFilePath',
		'flavorAssetId',
		'offset',
		'duration',
		'concatenatedDuration',
		'shouldSort',
	);

	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vConcatJobData();
			
		return parent::toObject($dbData, $props_to_skip);
	}
}
