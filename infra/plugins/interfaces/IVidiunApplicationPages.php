<?php
/**
 * Enable to plugin to add pages to external applications
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunApplicationPages extends IVidiunBase
{
	/**
	 * @return array
	 */
	public static function getApplicationPages();	
}