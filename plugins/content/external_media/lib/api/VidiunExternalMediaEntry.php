<?php
/**
 * @package plugins.externalMedia
 * @subpackage api.objects
 */
class VidiunExternalMediaEntry extends VidiunMediaEntry
{
	/**
	 * The source type of the external media
	 *
	 * @var VidiunExternalMediaSourceType
	 * @insertonly
	 * @filter eq,in
	 */
	public $externalSourceType;
	
	/**
	 * Comma separated asset params ids that exists for this external media entry
	 * 
	 * @var string
	 * @readonly
	 * @filter matchor,matchand
	 */
	public $assetParamsIds;
	
	private static $map_between_objects = array(
		'externalSourceType', 
		'assetParamsIds' => 'flavorParamsIds'
	);
	
	/**
	 * Define the entry type
	 */
	public function __construct()
	{
		$this->type = ExternalMediaPlugin::getApiValue(ExternalMediaEntryType::EXTERNAL_MEDIA);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseEntry::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseEntry::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(is_null($dbObject))
			$dbObject = new ExternalMediaEntry();
		
		return parent::toObject($dbObject, $skip);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunBaseEntry::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull('mediaType');
		$this->validatePropertyNotNull('externalSourceType');
		
		parent::validateForInsert($propertiesToSkip);
	}
}
