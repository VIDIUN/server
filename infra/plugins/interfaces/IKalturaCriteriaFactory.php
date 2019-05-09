<?php
/**
 * Enable the plugin to return extended VidiunCriteria object according to the searched object type
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunCriteriaFactory extends IVidiunBase
{
	/**
	 * Creates a new VidiunCriteria for the given object name
	 * 
	 * @param string $objectType object type to create Criteria for.
	 * @return VidiunCriteria derived object
	 */
	public static function getVidiunCriteria($objectType);
}