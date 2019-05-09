<?php
/**
 * Enable the plugin to define another plugin as a mandatory requirement for its load
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunRequire extends IVidiunBase
{
	/**
	 * Returns string(s) of Vidiun Plugins which the plugin requires
	 * 
	 * @return array<String> The Vidiun dependency object
	 */
	public static function requires();
}