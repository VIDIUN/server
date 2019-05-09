<?php
/**
 * @package plugins.drm
 */
class BaseDrmPlugin extends VidiunPlugin
{
	const BASE_PLUGIN_NAME = 'drm';
	/**
	 * @return string the name of the plugin
	 */
	public static function getPluginName(){}

	/**
	 * @param array<vRuleAction> $actions
	 * @return bool
	 */
    public static function shouldContributeToPlaybackContext(array $actions)
    {
	    foreach ($actions as $action)
	    {
		    /*** @var vRuleAction $action */
		    if ($action->getType() == DrmAccessControlActionType::DRM_POLICY)
			    return true;
	    }

	    return false;
    }
}


