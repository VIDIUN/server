<?php
/**
 * Enable ad cue point ingestion from XML bulk upload
 * @package plugins.adCuePoint
 */
class AdCuePointBulkUploadXmlPlugin extends VidiunPlugin implements IVidiunPending, IVidiunSchemaContributor
{
	const PLUGIN_NAME = 'adCuePointBulkUploadXml';
	const BULK_UPLOAD_XML_PLUGIN_NAME = 'bulkUploadXml';
	
	const BULK_UPLOAD_XML_VERSION_MAJOR = 1;
	const BULK_UPLOAD_XML_VERSION_MINOR = 1;
	const BULK_UPLOAD_XML_VERSION_BUILD = 0;

	/* (non-PHPdoc)
	 * @see VidiunPlugin::getInstance()
	 */
	public function getInstance($interface)
	{
		if($this instanceof $interface)
			return $this;
			
		if($interface == 'IVidiunBulkUploadXmlHandler')
			return AdCuePointBulkUploadXmlHandler::get();
			
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
		$bulkUploadXmlVersion = new VidiunVersion(
			self::BULK_UPLOAD_XML_VERSION_MAJOR,
			self::BULK_UPLOAD_XML_VERSION_MINOR,
			self::BULK_UPLOAD_XML_VERSION_BUILD);
			
		$bulkUploadXmlDependency = new VidiunDependency(self::BULK_UPLOAD_XML_PLUGIN_NAME, $bulkUploadXmlVersion);
		$adCuePointDependency = new VidiunDependency(AdCuePointPlugin::getPluginName());
		
		return array($bulkUploadXmlDependency, $adCuePointDependency);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSchemaContributor::contributeToSchema()
	 */
	public static function contributeToSchema($type)
	{
		$coreType = vPluginableEnumsManager::apiToCore('SchemaType', $type);
		if(
			$coreType != BulkUploadXmlPlugin::getSchemaTypeCoreValue(XmlSchemaType::BULK_UPLOAD_XML)
			&&
			$coreType != BulkUploadXmlPlugin::getSchemaTypeCoreValue(XmlSchemaType::BULK_UPLOAD_RESULT_XML)
		)
			return null;
	
		$xsd = '
		
	<!-- ' . self::getPluginName() . ' -->
	
	<xs:complexType name="T_scene_adCuePointBulkUploadXml">
		<xs:complexContent>
			<xs:extension base="T_scene">
				<xs:sequence>
					<xs:element name="sceneEndTime" minOccurs="0" maxOccurs="1" type="xs:time">
						<xs:annotation>
							<xs:documentation>A cue point that marks the end time</xs:documentation>
						</xs:annotation>
					</xs:element>
					<xs:element name="sceneTitle" minOccurs="0" maxOccurs="1">
						<xs:annotation>
							<xs:documentation>Text that defines the title</xs:documentation>
						</xs:annotation>
						<xs:simpleType>
							<xs:restriction base="xs:string">
								<xs:maxLength value="250"/>
							</xs:restriction>
						</xs:simpleType>
					</xs:element>
					<xs:element name="sourceUrl" minOccurs="0" maxOccurs="1" type="xs:string">
						<xs:annotation>
							<xs:documentation>The URL of the ad XML</xs:documentation>
						</xs:annotation>
					</xs:element>
					<xs:element name="adType" minOccurs="1" maxOccurs="1" type="VidiunAdType">
						<xs:annotation>
							<xs:documentation>Indicates the ad type</xs:documentation>
						</xs:annotation>
					</xs:element>
					<xs:element name="protocolType" minOccurs="1" maxOccurs="1" type="VidiunAdProtocolType">
						<xs:annotation>
							<xs:documentation>Indicates the ad protocol type</xs:documentation>
						</xs:annotation>
					</xs:element>
					
					<xs:element ref="scene-extension" minOccurs="0" maxOccurs="unbounded" />
				</xs:sequence>
			</xs:extension>
		</xs:complexContent>
	</xs:complexType>
	
	<xs:element name="scene-ad-cue-point" type="T_scene_adCuePointBulkUploadXml" substitutionGroup="scene">
		<xs:annotation>
			<xs:documentation>A single advertisement cue point element</xs:documentation>
			<xs:appinfo>
				<example>
					<scene-ad-cue-point systemName="MY_AD_CUE_POINT_SYSTEM_NAME">
						<sceneStartTime>00:00:05</sceneStartTime>
						<tags>
							<tag>tag1</tag>
							<tag>tag2</tag>
						</tags>
						<sceneTitle>my ad title</sceneTitle>
						<sourceUrl>http://source.to.my/ad.xml</sourceUrl>
						<adType>1</adType>
						<protocolType>1</protocolType>
					</scene-ad-cue-point>
				</example>
			</xs:appinfo>
		</xs:annotation>
	</xs:element>
		';
		
		return $xsd;
	}
}
