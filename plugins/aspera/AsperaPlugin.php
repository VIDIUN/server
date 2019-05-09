<?php
/**
 * @package plugins.aspera
 */
class AsperaPlugin extends VidiunPlugin implements IVidiunVersion, IVidiunPermissions, IVidiunServices
{

	const PLUGIN_NAME = 'aspera';
	
	const PLUGIN_VERSION_MAJOR = 1;
	const PLUGIN_VERSION_MINOR = 0;
	const PLUGIN_VERSION_BUILD = 0;
	
	/* (non-PHPdoc)
	 * @see VidiunPlugin::getInstance()
	 */
	public function getInstance($interface)
	{
		if($this instanceof $interface)
			return $this;

		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunVersion::getVersion()
	 */
	public static function getVersion()
	{
		return new VidiunVersion(
			self::PLUGIN_VERSION_MAJOR,
			self::PLUGIN_VERSION_MINOR,
			self::PLUGIN_VERSION_BUILD
		);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if(!$partner)
			return false;
			
		return $partner->getPluginEnabled(self::PLUGIN_NAME);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap()
	{
		$map = array(
			'aspera' => 'AsperaService',
		);
		return $map;
	}
}
