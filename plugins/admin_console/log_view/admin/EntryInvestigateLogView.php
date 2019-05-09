<?php
/**
 * @package plugins.logView
 * @subpackage admin
 */
class Vidiun_View_Helper_EntryInvestigateLogView extends Vidiun_View_Helper_PartialViewPlugin
{
	/* (non-PHPdoc)
	 * @see Vidiun_View_Helper_PartialViewPlugin::getDataArray()
	 */
	protected function getDataArray()
	{
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
		return 'entry-investigate-log-view.phtml';
	}
}