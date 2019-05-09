<?php
/**
 * Enable the plugin to add content distribution provider, AKA connector
 * @package plugins.contentDistribution
 * @subpackage lib
 */
interface IVidiunContentDistributionProvider extends IVidiunBase
{
	/**
	 * Returns the singelton instance of the plugin distribution provider.
	 * 
	 * @return IDistributionProvider
	 */
	public static function getProvider();
	
	/**
	 * Returns an instance of a Vidiun API distribution provider that represents the singleton instance of the plugin distribution provider.
	 * 
	 * @return VidiunDistributionProvider
	 */
	public static function getVidiunProvider();
	
	/**
	 * Appends nodes and attributes associated with a specific distribution provider and entry to an MRSS.
	 * 
	 * @param EntryDistribution $entryDistribution The distribution entry whose data is appended to the MRSS 
	 * @param SimpleXMLElement $mrss The MRSS to which the data is appended
	 */
	public static function contributeMRSS(EntryDistribution $entryDistribution, SimpleXMLElement $mrss);
}