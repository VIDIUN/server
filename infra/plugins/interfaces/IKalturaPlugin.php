<?php
/**
 * Must be implemented by all plugins
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunPlugin extends IVidiunBase
{
	/**
	 * @return string the name of the plugin
	 */
	public static function getPluginName();
}