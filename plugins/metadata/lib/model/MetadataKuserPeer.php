<?php
/**
 * @package plugins.metadata
 * @subpackage model
 */
class MetadataVuserPeer extends vuserPeer implements IMetadataPeer
{
    public static function validateMetadataObjects($profileField, $objectIds, &$errorMessage)
    {
        /** @var MetadataProfileField $profileField */
        
        $partnerId = vCurrentContext::getCurrentPartnerId();
        $dbObjects = vuserPeer::getVuserByPartnerAndUids($partnerId, $objectIds);
        
        if(count($dbObjects) != count($objectIds))
        {
            $errorMessage = 'One of the following objects: '.implode(', ', $objectIds).' was not found';
            return false;
        }
        
        return true;
    }

    public static function getEntry($objectId)
    {
        return null;
    }
    
    public static function validateMetadataObjectAccess($objectId)
    {
    	$vuser = self::retrieveByPK($objectId);
    	if(!$vuser)
    	{
    		VidiunLog::debug("Metadata object id with id [$objectId] not found");
    		return false;
    	}
    
    	return true;
    }
}