<?php
/**
 * Marker interface
 *
 * @package Core
 * @subpackage enum
 */ 
interface IVidiunPluginEnum 
{
	/**
	 * @return array
	 */
	public static function getAdditionalValues();
	
	/**
	* @return array
	*/
	public static function getAdditionalDescriptions();
}
