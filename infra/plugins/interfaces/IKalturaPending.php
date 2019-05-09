<?php
/**
 * Enable the plugin to define dependency on another plugin
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunPending extends IVidiunBase
{
	/**
	 * Returns a Vidiun dependency object that defines the relationship between two plugins.
	 * 
	 * @return array<VidiunDependency> The Vidiun dependency object
	 */
	public static function dependsOn();
}