<?php
/**
 * @package plugins.metadata
 * @subpackage model
 */
class MetadataCategoryPeer extends categoryPeer implements IMetadataPeer
{
    public static function validateMetadataObjects($profileField, $objectIds, &$errorMessage)
    {
        return true;
    }

    public static function getEntry($objectId)
    {
        return null;
    }
    
    public static function validateMetadataObjectAccess($objectId)
    {
    	$categoryDb = self::retrieveByPK($objectId);
    	if(!$categoryDb)
    	{
    		VidiunLog::debug("Metadata object id with id [$objectId] not found");
    		return false;
    	}
    	
    	if (vEntitlementUtils::getEntitlementEnforcement())
    	{
    		$currentVuserCategoryVuser = categoryVuserPeer::retrievePermittedVuserInCategory($categoryDb->getId(), vCurrentContext::getCurrentVsVuserId(), array(PermissionName::CATEGORY_EDIT));
    		if(!$currentVuserCategoryVuser || $currentVuserCategoryVuser->getPermissionLevel() != CategoryVuserPermissionLevel::MANAGER)
    		{
    			VidiunLog::debug("Current user is not permitted to access category with id [$objectId]");
    			return false;
    		}
    	}
    	
    	return true;
    }
}
