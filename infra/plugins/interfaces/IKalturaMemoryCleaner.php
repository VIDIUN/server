<?php
/**
 * Enable the plugin to clean unused memory, instances and pools
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunMemoryCleaner extends IVidiunBase
{
	public static function cleanMemory();
}