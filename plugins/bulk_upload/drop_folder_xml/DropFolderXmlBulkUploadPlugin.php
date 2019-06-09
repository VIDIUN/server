<?php
/**
 * @package plugins.dropFolderXmlBulkUpload
 */
class DropFolderXmlBulkUploadPlugin extends VidiunPlugin implements IVidiunBulkUpload, IVidiunPending, IVidiunSchemaDefiner, IVidiunEventConsumers
{
	const PLUGIN_NAME = 'dropFolderXmlBulkUpload';
	const XML_BULK_UPLOAD_PLUGIN_VERSION_MAJOR = 1;
	const XML_BULK_UPLOAD_PLUGIN_VERSION_MINOR = 1;
	const XML_BULK_UPLOAD_PLUGIN_VERSION_BUILD = 0;	
	const DROP_FOLDER_XML_EVENTS_CONSUMER = 'vDropFolderXmlEventsConsumer';
	
	//Error Messages
	const ERROR_ADDING_BULK_UPLOAD_MESSAGE = 'Failed to create bulk upload job in Vidiun';
	const ERROR_IN_BULK_UPLOAD_MESSAGE = 'Failed  to execute the bulk upload job in Vidiun';
	const ERROR_ADD_CONTENT_RESOURCE_MESSAGE = 'Failed to add drop folder content resource files';
	const MALFORMED_XML_FILE_MESSAGE = 'Failed to handle  XML File.  Invalid XML format.';
	const XML_FILE_SIZE_EXCEED_LIMIT_MESSAGE = 'Failed to handle XML file. XML file size exceeds the supported 10 MB limit';
	
	
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
		$bulkUploadXmlVersion = new VidiunVersion(
			self::XML_BULK_UPLOAD_PLUGIN_VERSION_MAJOR,
			self::XML_BULK_UPLOAD_PLUGIN_VERSION_MINOR,
			self::XML_BULK_UPLOAD_PLUGIN_VERSION_BUILD);
			
		$bulkUploadXmlDependency = new VidiunDependency(BulkUploadXmlPlugin::getPluginName(), $bulkUploadXmlVersion);
		$dropFolderDependency = new VidiunDependency(DropFolderPlugin::getPluginName());
		
		return array($bulkUploadXmlDependency, $dropFolderDependency);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('DropFolderXmlBulkUploadType', 'DropFolderXmlFileHandlerType', 'DropFolderXmlBulkUploadErrorCode', 'DropFolderXmlSchemaType', 'DropFolderBatchJobObjectType');
		
		if($baseEnumName == 'BulkUploadType')
			return array('DropFolderXmlBulkUploadType');
		
		if($baseEnumName == 'DropFolderFileHandlerType')
			return array('DropFolderXmlFileHandlerType');
			
		if($baseEnumName == 'DropFolderFileErrorCode')
			return array('DropFolderXmlBulkUploadErrorCode');
			
		if($baseEnumName == 'SchemaType')
			return array('DropFolderXmlSchemaType');

		if($baseEnumName == 'BatchJobObjectType')
			return array('DropFolderBatchJobObjectType');
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		//Gets the right job for the engine
		if($baseClass == 'VidiunDropFolderBulkUploadJobData' && $enumValue == self::getBulkUploadTypeCoreValue(DropFolderXmlBulkUploadType::DROP_FOLDER_XML))
			return new VidiunDropFolderXmlBulkUploadJobData();

		//Gets the right job for the engine
		if($baseClass == 'VidiunBulkUploadJobData' && $enumValue == self::getBulkUploadTypeCoreValue(DropFolderXmlBulkUploadType::DROP_FOLDER_XML))
			return new VidiunDropFolderXmlBulkUploadJobData();

		//Gets the right job for the engine	
		if($baseClass == 'vBulkUploadJobData' && $enumValue == self::getBulkUploadTypeCoreValue(DropFolderXmlBulkUploadType::DROP_FOLDER_XML))
			return new vDropFolderBulkUploadXmlJobData();

		//Gets the right job for the engine
		if($baseClass == 'vDropFolderBulkUploadXmlJobData' && $enumValue == self::getBulkUploadTypeCoreValue(DropFolderXmlBulkUploadType::DROP_FOLDER_XML))
			return new vDropFolderBulkUploadXmlJobData();

		//Gets the engine (only for clients)
		if($baseClass == 'VBulkUploadEngine' && class_exists('VidiunClient') && $enumValue == VidiunBulkUploadType::DROP_FOLDER_XML)
		{
			list($job) = $constructorArgs;
			return new DropFolderXmlBulkUploadEngine($job);
		}
		
		if ($baseClass == 'VidiunDropFolderFileHandlerConfig' && $enumValue == self::getFileHandlerTypeCoreValue(DropFolderXmlFileHandlerType::XML))
			return new VidiunDropFolderXmlBulkUploadFileHandlerConfig();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		return null;
	}
	
	/**
	 * Returns the log file for bulk upload job
	 * @param BatchJob $batchJob bulk upload batchjob
	 */
	public static function writeBulkUploadLogFile($batchJob)
	{
		if($batchJob->getJobSubType() != self::getBulkUploadTypeCoreValue(DropFolderXmlBulkUploadType::DROP_FOLDER_XML)){
			return;
		}
		
		$xmlElement = BulkUploadXmlPlugin::getBulkUploadMrssXml($batchJob);
		if(is_null($xmlElement)){
			echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><mrss><error>Log file is not ready: ".$batchJob->getMessage()."</error></mrss>";
			vFile::closeDbConnections();
			exit;
		}
		echo $xmlElement->asXML();
		vFile::closeDbConnections();
		exit;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunBulkUpload::getFileExtension()
	 */
	public static function getFileExtension($enumValue)
	{
		if($enumValue == self::getBulkUploadTypeCoreValue(DropFolderXmlBulkUploadType::DROP_FOLDER_XML))
			return 'xml';
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSchemaContributor::getPluginSchema()
	 */
	public static function getPluginSchema($type)
	{
		$coreType = vPluginableEnumsManager::apiToCore('SchemaType', $type);
		if($coreType != self::getSchemaTypeCoreValue(DropFolderXmlSchemaType::DROP_FOLDER_XML))
			return null;
			
		$xmlApiType = BulkUploadXmlPlugin::getApiValue(XmlSchemaType::BULK_UPLOAD_XML);
		$baseXsdElement = BulkUploadXmlPlugin::getPluginSchema($xmlApiType);
			
		$xsd = '<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">';
	
		foreach($baseXsdElement->children('http://www.w3.org/2001/XMLSchema') as $element)
		{
			/* @var $element SimpleXMLElement */
			$xsd .= '
	
	' . $element->asXML();
		}
		
		$xsd .= '
				
	<xs:complexType name="T_dropFolderFileContentResource">
		<xs:choice minOccurs="0" maxOccurs="1">
			<xs:element name="fileSize" type="xs:long" minOccurs="1" maxOccurs="1">
				<xs:annotation>
					<xs:documentation>
						The expected size of the file<br/>
						Used for validation
					</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="fileChecksum" minOccurs="1" maxOccurs="1">
				<xs:annotation>
					<xs:documentation>
						The expected checksum of the file<br/>
						md5 or sha1<br/>
						Used for validation
					</xs:documentation>
				</xs:annotation>
				<xs:complexType>
					<xs:simpleContent>
						<xs:extension base="xs:string">
							<xs:attribute name="type" use="optional" default="md5">				
								<xs:simpleType>
									<xs:restriction base="xs:string">
										<xs:enumeration value="md5"/>
										<xs:enumeration value="sha1"/>
									</xs:restriction>
								</xs:simpleType>
							</xs:attribute>
						</xs:extension>
					</xs:simpleContent>
				</xs:complexType>
			</xs:element>
		</xs:choice>
		<xs:attribute name="filePath" use="required">
			<xs:annotation>
				<xs:documentation>
					The name of the file in the drop folder
				</xs:documentation>
			</xs:annotation>
			<xs:simpleType>
				<xs:restriction base="xs:string">
					<xs:maxLength value="500"/>
				</xs:restriction>
			</xs:simpleType>
		</xs:attribute>
		<xs:attribute name="dropFolderFileId" type="xs:string" use="optional">
			<xs:annotation>
				<xs:documentation>
					The id of the drop folder file object
				</xs:documentation>
			</xs:annotation>
		</xs:attribute>
	</xs:complexType>

	<xs:element name="dropFolderFileContentResource" type="T_dropFolderFileContentResource" substitutionGroup="contentResource-extension">
		<xs:annotation>
			<xs:documentation>Specifies that content file location is within a Vidiun defined drop folder</xs:documentation>
			<xs:appinfo>
				<example title="Using file size validation example">
					<item>
						<action>add</action>
						<type>1</type>
						<media>...</media>
						<content>...</content>
						<thumbnail>
							<dropFolderFileContentResource filePath="file.jpg">
								<fileSize>453453344</fileSize>
							</dropFolderFileContentResource>
						</thumbnail>
					</item>
				</example>
				<example title="Using checksum validation example">
					<item>
						<action>add</action>
						<type>1</type>
						<media>...</media>
						<content>...</content>
						<thumbnail>
							<dropFolderFileContentResource filePath="file.jpg">
								<fileChecksum type="md5">sdfsjodf90sfsdfzfasdfwrg34</fileChecksum>
							</dropFolderFileContentResource>
						</thumbnail>
					</item>
				</example>
			</xs:appinfo>
		</xs:annotation>
	</xs:element>
	
	';
	
		$schemaContributors = VidiunPluginManager::getPluginInstances('IVidiunSchemaContributor');
		foreach($schemaContributors as $key => $schemaContributor)
		{
			/* @var $schemaContributor IVidiunSchemaContributor */
			$elements = $schemaContributor->contributeToSchema($xmlApiType);
			if($elements)
				$xsd .= $elements;
		}
		
		$xsd .= '
</xs:schema>';
		
		return new SimpleXMLElement($xsd);
	}
		
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getBulkUploadTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('BulkUploadType', $value);
	}
		
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getSchemaTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('SchemaType', $value);
	}
		
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getFileHandlerTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('DropFolderFileHandlerType', $value);
	}
	
	public static function getErrorCodeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('DropFolderFileErrorCode', $value);
	}
	
	public static function getBatchJobObjectTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('BatchJobObjectType', $value);
	}
		
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	public static function getEventConsumers()
	{
		return array(
			self::DROP_FOLDER_XML_EVENTS_CONSUMER,
		);
	}
}
