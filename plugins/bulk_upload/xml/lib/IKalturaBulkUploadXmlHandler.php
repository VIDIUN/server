<?php
/**
 * Plugins can handle bulk upload xml additional data by implementing this interface
 * @package plugins.bulkUploadXml
 * @subpackage lib
 */
interface IVidiunBulkUploadXmlHandler
{
	/**
	 * Configures the handler by passing all the required configuration 
	 * @param BulkUploadEngineXml $xmlBulkUploadEngine  
	 */
	public function configureBulkUploadXmlHandler(BulkUploadEngineXml $xmlBulkUploadEngine);
	
	/**
	 * Handles plugin data for new created object 
	 * @param VidiunObjectBase $object
	 * @param SimpleXMLElement $item
	 * @throws VidiunBulkUploadXmlException  
	 */
	public function handleItemAdded(VidiunObjectBase $object, SimpleXMLElement $item);

	/**
	 * 
	 * Handles plugin data for updated object  
	 * @param VidiunObjectBase $object
	 * @param SimpleXMLElement $item
	 * @throws VidiunBulkUploadXmlException  
	 */
	public function handleItemUpdated(VidiunObjectBase $object, SimpleXMLElement $item);

	/**
	 * 
	 * Handles plugin data for deleted object  
	 * @param VidiunObjectBase $object
	 * @param SimpleXMLElement $item
	 * @throws VidiunBulkUploadXmlException  
	 */
	public function handleItemDeleted(VidiunObjectBase $object, SimpleXMLElement $item);
	
	/**
	 * Return the container name to be handeled
	 */
	public function getContainerName();
}