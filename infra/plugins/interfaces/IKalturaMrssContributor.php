<?php
/**
 * Enable the plugin to add additional XML nodes and attributes to entry MRSS
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunMrssContributor extends IVidiunBase
{
	/**
	 * @param BaseObject $object
	 * @param SimpleXMLElement $mrss
	 * @param vMrssParameters $mrssParams
	 * @return SimpleXMLElement
	 */
	public function contribute(BaseObject $object, SimpleXMLElement $mrss, vMrssParameters $mrssParams = null);	

	/**
	 * Function returns the object feature type for the use of the VmrssManager
	 * 
	 * @return int
	 */
	public function getObjectFeatureType ();
}