<?php
/**
 * @package plugins.kontiki
 * @subpackage api.object
 */
class VidiunKontikiStorageProfile extends VidiunStorageProfile
{
	
	/**
	 * @var string
	 */
	public $serviceToken;
	
	
	private static $map_between_objects = array
	(
		'serviceToken',
		
	);
	
	/* (non-PHPdoc)
	 * @see VidiunObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}	
	
	public function toObject ($dbObject = null, $props_to_skip = array())
	{
	    /* @var $dbObject VidiunStorageProfile */
		if (!$dbObject)
		{
			$dbObject = new KontikiStorageProfile();
		}
		
		$dbObject->setProtocol(KontikiPlugin::getStorageProfileProtocolCoreValue(KontikiStorageProfileProtocol::KONTIKI));
        
		return parent::toObject($dbObject, $props_to_skip);
	}
    
    /* (non-PHPdoc)
     * @see VidiunObject::toInsertableObject()
     */
    public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
    {
        if(is_null($object_to_fill))
            $object_to_fill = new KontikiStorageProfile();
            
        return parent::toInsertableObject($object_to_fill, $props_to_skip);
    }
	
	/* (non-PHPdoc)
     * @see VidiunObject::validateForInsert()
     */
	public function validateForInsert ($propertiesToSkip = array())
	{
		if (!KontikiPlugin::isAllowedPartner(vCurrentContext::getCurrentPartnerId()))
		{
			throw new VidiunAPIException(VidiunErrors::PERMISSION_NOT_FOUND, 'Kontiki permission not found for partner');
		}
	}

}
