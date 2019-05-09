<?php
/**
 * Enable log view for admin-console entry investigation page
 * @package plugins.logView
 */
class LogViewPlugin extends VidiunPlugin implements IVidiunApplicationPartialView, IVidiunAdminConsolePages
{
	const PLUGIN_NAME = 'logView';

	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunApplicationPartialView::getApplicationPartialViews()
	 */
	public static function getApplicationPartialViews($controller, $action)
	{
		if($controller == 'batch' && $action == 'entryInvestigation')
		{
			return array(
				new Vidiun_View_Helper_EntryInvestigateLogView(),
			);
		}
		
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunAdminConsolePages::getApplicationPages()
	 */
	public static function getApplicationPages()
	{
		$VidiunInternalTools = array(
			new VidiunLogViewAction(),
			new VidiunObjectInvestigateLogAction(),
		);
		return $VidiunInternalTools;
	}
}
