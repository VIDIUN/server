<?php
/**
 * @package plugins.group
 */
class GroupPlugin extends VidiunPlugin implements IVidiunServices, IVidiunPermissions, IVidiunPending
{
	const PLUGIN_NAME = 'group';

	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}

	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		return true;
	}

	public static function dependsOn()
	{
		$eSearchDependency = new VidiunDependency(ElasticSearchPlugin::getPluginName());
		return array($eSearchDependency);
	}

	public static function getServicesMap ()
	{
		$map = array(
			'group' => 'GroupService',
		);
		return $map;
	}
}