<?php
/**
 * @package plugins.varConsole
 */
class VarConsolePlugin extends VidiunPlugin implements IVidiunServices, IVidiunPermissions
{
    const PLUGIN_NAME = "varConsole";

	/* (non-PHPdoc)
     * @see IVidiunPlugin::getPluginName()
     */
    public static function getPluginName ()
    {    
        return self::PLUGIN_NAME;
    }


	/* (non-PHPdoc)
     * @see IVidiunServices::getServicesMap()
     */
    public static function getServicesMap ()
    {
        $map = array(
			'varConsole' => 'VarConsoleService',
		);
		
		return $map;
    }
    
    /* (non-PHPdoc)
     * @see IVidiunPermissions::isAllowedPartner($partnerId)
     */
    public static function isAllowedPartner($partnerId)
    {
        $partner = PartnerPeer::retrieveByPK($partnerId);
		
		return $partner->getEnabledService(VidiunPermissionName::FEATURE_VAR_CONSOLE_LOGIN);
    }

}