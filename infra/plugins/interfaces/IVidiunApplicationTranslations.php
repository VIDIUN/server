<?php
/**
 * Enable to plugin to add translated keys
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunApplicationTranslations extends IVidiunBase
{
	/**
	 * @return array
	 */
	public static function getTranslations($locale);	
}