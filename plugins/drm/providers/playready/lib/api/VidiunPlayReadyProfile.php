<?php
/**
 * @package plugins.playReady
 * @subpackage api.objects
 */
class VidiunPlayReadyProfile extends VidiunDrmProfile
{
    /**
	 * @var string
	 */
	public $keySeed;	
	
	private static $map_between_objects = array(
		'keySeed',
	 );
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new PlayReadyProfile();
			
		parent::toObject($dbObject, $skip);
					
		return $dbObject;
	}
	
	public function validateForInsert($propertiesToSkip = array())
	{
		if (!PlayReadyPlugin::isAllowedPartner(vCurrentContext::getCurrentPartnerId()) || !PlayReadyPlugin::isAllowedPartner($this->partnerId))
		{
			throw new VidiunAPIException (VidiunErrors::PERMISSION_NOT_FOUND, 'Permission not found to use the PlayReady feature.');
		}
		return parent::validateForInsert($propertiesToSkip);
	}
	
	public function validateForUpdate ($sourceObject, $propertiesToSkip = array())
	{
		if (!PlayReadyPlugin::isAllowedPartner(vCurrentContext::getCurrentPartnerId()) || !PlayReadyPlugin::isAllowedPartner($sourceObject->getPartnerId()))
		{
			throw new VidiunAPIException (VidiunErrors::PERMISSION_NOT_FOUND, 'Permission not found to use the PlayReady feature.');
		}
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
}

