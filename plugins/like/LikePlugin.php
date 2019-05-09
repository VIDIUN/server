<?php
/**
 * Enable 'liking' or 'unliking' an entry as the current user, rather than anonymously ranking it.
 * @package plugins.like
 */
class LikePlugin extends VidiunPlugin implements IVidiunServices, IVidiunPermissions
{
    const PLUGIN_NAME = "like";
    
	/* (non-PHPdoc)
     * @see IVidiunServices::getServicesMap()
     */
    public static function getServicesMap ()
    {
        $map = array(
			'like' => 'LikeService',
		);
		return $map;
    }

	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		
		return $partner->getEnabledService(VidiunPermissionName::FEATURE_LIKE);
	}
	

	/* (non-PHPdoc)
     * @see IVidiunPlugin::getPluginName()
     */
    public static function getPluginName ()
    {
        return self::PLUGIN_NAME;
    }

    
}