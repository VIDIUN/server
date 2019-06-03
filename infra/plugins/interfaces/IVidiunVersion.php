<?php
/**
 * Enable you to give version to the plugin
 * The version might be importent for depencies between different plugins
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunVersion extends IVidiunBase
{
	/**
	 * @return VidiunVersion
	 */
	public static function getVersion();
}