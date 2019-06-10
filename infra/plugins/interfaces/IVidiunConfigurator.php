<?php
/**
 * Enable the plugin to append configuration to existing server configuration
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunConfigurator extends IVidiunBase
{
	/**
	 * Merge configuration data from the plugin
	 * 
	 * @param string $configName
	 * @return Iterator
	 */
	public static function getConfig($configName);
}