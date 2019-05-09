<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunPostConvertJobData extends VidiunConvartableJobData
{
	/**
	 * @var string
	 */
	public $flavorAssetId;

	/**
	* @var string
	*/
	public $flavorAssetEncryptionKey;

	/**
	 * Indicates if a thumbnail should be created
	 * 
	 * @var bool
	 */
	public $createThumb;
	
	/**
	 * The path of the created thumbnail
	 *  
	 * @var string
	 */
	public $thumbPath;
	
	/**
	 * The position of the thumbnail in the media file
	 *  
	 * @var int
	 */
	public $thumbOffset;
	
	/**
	 * The height of the movie, will be used to comapare if this thumbnail is the best we can have
	 *  
	 * @var int
	 */
	public $thumbHeight;
	
	/**
	 * The bit rate of the movie, will be used to comapare if this thumbnail is the best we can have
	 *  
	 * @var int
	 */
	public $thumbBitrate;
		
	/**
	 * @var string
	 */
	public $customData;
	
	private static $map_between_objects = array
	(
		"flavorAssetId" ,
		"createThumb" ,
		"thumbPath" ,
		"flavorAssetEncryptionKey",
		"thumbOffset" ,
		"thumbHeight" ,
		"thumbBitrate" ,
		"customData",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vPostConvertJobData();

		return parent::toObject($dbData);
	}

	/**
	 * @param string $subType from enum VidiunMediaParserType
	 * @return int from enum mediaParserType
	 */
	public function toSubType($subType)
	{
		return vPluginableEnumsManager::apiToCore('mediaParserType', $subType);
	}
	
	/**
	 * @param int $subType from enum mediaParserType
	 * @return string from enum VidiunMediaParserType
	 */
	public function fromSubType($subType)
	{
		return vPluginableEnumsManager::coreToApi('mediaParserType', $subType);
	}
}
