<?php
/**
 * @package plugins.playReady
 * @subpackage admin
 */ 
class Vidiun_View_Helper_PlayReadyProfileConfigure extends Vidiun_View_Helper_PartialViewPlugin
{
	/* (non-PHPdoc)
	 * @see Vidiun_View_Helper_PartialViewPlugin::getDataArray()
	 */
	protected function getDataArray()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see Vidiun_View_Helper_PartialViewPlugin::getTemplatePath()
	 */
	protected function getTemplatePath()
	{
		return realpath(dirname(__FILE__));
	}
	
	/* (non-PHPdoc)
	 * @see Vidiun_View_Helper_PartialViewPlugin::getPHTML()
	 */
	protected function getPHTML()
	{
		return 'play-ready-provider-configure-action.phtml';
	}
}