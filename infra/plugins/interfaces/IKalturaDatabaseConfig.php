<?php
/**
 * Enable you to add database connections
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunDatabaseConfig extends IVidiunBase
{
	/**
	 * @return array
	 */
	public static function getDatabaseConfig();	
}