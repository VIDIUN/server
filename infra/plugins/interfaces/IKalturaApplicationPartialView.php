<?php
/**
 * Enable the plugin to add phtml view to existing page
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunApplicationPartialView extends IVidiunBase
{
	/**
	 * @return array<Vidiun_View_Helper_PartialViewPlugin>
	 */
	public static function getApplicationPartialViews($controller, $action);
}