<?php
/**
 * @package plugins.metadata
 * @subpackage api.objects
 */
class VidiunMetadataField extends VidiunStringField
{
	/**
	 * May contain the full xpath to the field in three formats
	 * 1. Slashed xPath, e.g. /metadata/myElementName
	 * 2. Using local-name function, e.g. /*[local-name()='metadata']/*[local-name()='myElementName']
	 * 3. Using only the field name, e.g. myElementName, it will be searched as //myElementName
	 *
	 * @var string
	 */
	public $xPath;
	
	/**
	 * Metadata profile id
	 * @var int
	 */
	public $profileId;
	
	/**
	 * Metadata profile system name
	 * @var string
	 */
	public $profileSystemName;
	
	private static $mapBetweenObjects = array
	(
		'xPath',
		'profileId',
		'profileSystemName',
	);
	
	/* (non-PHPdoc)
	 * @see VidiunMatchCondition::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$mapBetweenObjects);
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::validateForUsage()
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);
		
		$this->validatePropertyNotNull('xPath');
		$this->validatePropertyNotNull(array('profileId', 'profileSystemName'));
	}
	
	/* (non-PHPdoc)
	 * @see VidiunObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new vMetadataField();
		
		return parent::toObject($dbObject, $skip);
	}
}
