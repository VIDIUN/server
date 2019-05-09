<?php
/**
 * Enable custom metadata on annotation objects
 * @package plugins.annotation
 */
class AnnotationMetadataPlugin extends VidiunPlugin implements IVidiunPending, IVidiunObjectLoader, IVidiunCuePointXmlParser, IVidiunEnumerator
{
	const PLUGIN_NAME = 'annotationMetadata';
	const METADATA_BULK_UPLOAD_XML_PLUGIN_NAME = 'metadataBulkUploadXml';
	
	/* (non-PHPdoc)
	 * @see VidiunPlugin::getInstance()
	 */
	public function getInstance($interface)
	{
		if($this instanceof $interface)
			return $this;
			
		if($interface == 'IVidiunBulkUploadXmlHandler')
			return new MetadataBulkUploadXmlEngineHandler(VidiunMetadataObjectType::ANNOTATION, 'VidiunAnnotation', 'scene-customData');
			
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
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
		$cuePointMetadataDependency = new VidiunDependency(CuePointMetadataPlugin::getPluginName());
		$metadataBulkUploadXmlDependency = new VidiunDependency(self::METADATA_BULK_UPLOAD_XML_PLUGIN_NAME);
		
		return array($cuePointMetadataDependency, $metadataBulkUploadXmlDependency);
	}

	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('AnnotationMetadataObjectType');
	
		if($baseEnumName == 'MetadataObjectType')
			return array('AnnotationMetadataObjectType');
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		$class = self::getObjectClass($baseClass, $enumValue);
		if($class && class_exists($class))
			return new $class();
			
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'IMetadataPeer' && $enumValue == self::getMetadataObjectTypeCoreValue(AnnotationMetadataObjectType::ANNOTATION))
			return 'CuePointPeer';
			
		if($baseClass == 'IMetadataObject' && $enumValue == self::getMetadataObjectTypeCoreValue(AnnotationMetadataObjectType::ANNOTATION))
			return 'Annotation';
	}

	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getMetadataObjectTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('MetadataObjectType', $value);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePointXmlParser::parseXml()
	 */
	public static function parseXml(SimpleXMLElement $scene, $partnerId, CuePoint $cuePoint = null)
	{
		if(is_null($cuePoint) || $scene->getName() != 'scene-annotation' || !($cuePoint instanceof Annotation))
			return $cuePoint;
			
		$objectType = self::getMetadataObjectTypeCoreValue(AnnotationMetadataObjectType::ANNOTATION);
		return CuePointMetadataPlugin::parseXml($objectType, $scene, $partnerId, $cuePoint);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePointXmlParser::generateXml()
	 */
	public static function generateXml(CuePoint $cuePoint, SimpleXMLElement $scenes, SimpleXMLElement $scene = null)
	{
		if(is_null($scene) || $scene->getName() != 'scene-annotation' || !($cuePoint instanceof Annotation))
			return $scene;
			
		$objectType = self::getMetadataObjectTypeCoreValue(AnnotationMetadataObjectType::ANNOTATION);
		return CuePointMetadataPlugin::generateCuePointXml($scene, $objectType, $cuePoint->getId());
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePointXmlParser::syndicate()
	 */
	public static function syndicate(CuePoint $cuePoint, SimpleXMLElement $scenes, SimpleXMLElement $scene = null)
	{
		self::generateXml($cuePoint, $scenes, $scene);
	}
}
