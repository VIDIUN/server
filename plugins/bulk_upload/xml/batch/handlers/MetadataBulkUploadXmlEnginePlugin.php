<?php
/**
 * @package plugins.metadataBulkUploadXml
 */
class MetadataBulkUploadXmlEnginePlugin extends VidiunPlugin implements IVidiunPending, IVidiunConfigurator
{
	const PLUGIN_NAME = 'metadataBulkUploadXmlEngine';
	
	const BULK_UPLOAD_XML_VERSION_MAJOR = 1;
	const BULK_UPLOAD_XML_VERSION_MINOR = 0;
	const BULK_UPLOAD_XML_VERSION_BUILD = 0;
	
	/* (non-PHPdoc)
	 * @see VidiunPlugin::getInstance()
	 */
	public function getInstance($interface)
	{
		if($this instanceof $interface)
			return $this;
			
		if($interface == 'IVidiunBulkUploadXmlHandler')
			return new MetadataBulkUploadXmlEngineHandler(VidiunMetadataObjectType::ENTRY, 'VidiunBaseEntry', 'customData', 'customDataItems');
			
		return null;
	}
	
	/**
	 * @return string
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$bulkUploadXmlVersion = new VidiunVersion(
			self::BULK_UPLOAD_XML_VERSION_MAJOR,
			self::BULK_UPLOAD_XML_VERSION_MINOR,
			self::BULK_UPLOAD_XML_VERSION_BUILD);
			
		$bulkUploadXmlDependency = new VidiunDependency(BulkUploadXmlPlugin::getPluginName(), $bulkUploadXmlVersion);
		$metadataDependency = new VidiunDependency(MetadataPlugin::getPluginName());
		
		return array($bulkUploadXmlDependency, $metadataDependency);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunConfigurator::getConfig()
	 */
	public static function getConfig($configName)
	{
		if($configName == 'generator')
			return new Zend_Config_Ini(dirname(__FILE__) . '/config/metadataBulkUploadXml.generator.ini');
			
		return null;
	}
}
