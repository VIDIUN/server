<?php
/**
 * @package plugins.fairplay
 * @subpackage api.objects
 */
class VidiunFairplayDrmProfile extends VidiunDrmProfile
{
    /**
     * @var string
	 */
	public $publicCertificate;	
	
	private static $map_between_objects = array(
		'publicCertificate',
	 );
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new FairplayDrmProfile();
			
		parent::toObject($dbObject, $skip);
					
		return $dbObject;
	}
	
	public function validateForInsert($propertiesToSkip = array())
	{
		if (!DrmPlugin::isAllowedPartner(vCurrentContext::getCurrentPartnerId()) || !DrmPlugin::isAllowedPartner($this->partnerId))
		{
			throw new VidiunAPIException (VidiunErrors::PERMISSION_NOT_FOUND, 'Permission not found to use the PlayReady feature.');
		}
		return parent::validateForInsert($propertiesToSkip);
	}
	
	public function validateForUpdate ($sourceObject, $propertiesToSkip = array())
	{
		if (!DrmPlugin::isAllowedPartner(vCurrentContext::getCurrentPartnerId()) || !DrmPlugin::isAllowedPartner($sourceObject->getPartnerId()))
		{
			throw new VidiunAPIException (VidiunErrors::PERMISSION_NOT_FOUND, 'Permission not found to use the PlayReady feature.');
		}
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
}

