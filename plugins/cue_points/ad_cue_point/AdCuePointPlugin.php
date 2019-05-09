<?php
/**
 * Enable ad cue point objects management on entry objects
 * @package plugins.adCuePoint
 */
class AdCuePointPlugin extends BaseCuePointPlugin implements IVidiunCuePoint, IVidiunCuePointXmlParser
{
	const PLUGIN_NAME = 'adCuePoint';
	const CUE_POINT_VERSION_MAJOR = 1;
	const CUE_POINT_VERSION_MINOR = 0;
	const CUE_POINT_VERSION_BUILD = 0;
	const CUE_POINT_NAME = 'cuePoint';
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		$partner = PartnerPeer::retrieveByPK($partnerId);
		return $partner->getPluginEnabled(self::PLUGIN_NAME);
	}

	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('AdCuePointType', 'BaseEntryAdCuePointCloneOptions');
	
		if($baseEnumName == 'CuePointType')
			return array('AdCuePointType');

		if($baseEnumName == 'BaseEntryCloneOptions')
			return array('BaseEntryAdCuePointCloneOptions');
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$cuePointVersion = new VidiunVersion(
			self::CUE_POINT_VERSION_MAJOR,
			self::CUE_POINT_VERSION_MINOR,
			self::CUE_POINT_VERSION_BUILD);
			
		$dependency = new VidiunDependency(self::CUE_POINT_NAME, $cuePointVersion);
		return array($dependency);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'VidiunCuePoint' && $enumValue == self::getCuePointTypeCoreValue(AdCuePointType::AD))
			return new VidiunAdCuePoint();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'CuePoint' && $enumValue == self::getCuePointTypeCoreValue(AdCuePointType::AD))
			return 'AdCuePoint';
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSchemaContributor::contributeToSchema()
	 */
	public static function contributeToSchema($type)
	{
		$coreType = vPluginableEnumsManager::apiToCore('SchemaType', $type);
		if(
			$coreType != SchemaType::SYNDICATION
			&&
			$coreType != CuePointPlugin::getSchemaTypeCoreValue(CuePointSchemaType::SERVE_API)
			&&
			$coreType != CuePointPlugin::getSchemaTypeCoreValue(CuePointSchemaType::INGEST_API)
		)
			return null;
			
		$xsd = '		
		
	<!-- ' . self::getPluginName() . ' -->
		
	<xs:complexType name="T_scene_adCuePoint">
		<xs:complexContent>
			<xs:extension base="T_scene">
				<xs:sequence>
					<xs:element name="sceneEndTime" minOccurs="0" maxOccurs="1" type="xs:time">
						<xs:annotation>
							<xs:documentation>Cue point end time</xs:documentation>
						</xs:annotation>
					</xs:element>
					<xs:element name="sceneTitle" minOccurs="0" maxOccurs="1">
						<xs:annotation>
							<xs:documentation>Textual title</xs:documentation>
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
	
	<xs:element name="scene-ad-cue-point" type="T_scene_adCuePoint" substitutionGroup="scene">
		<xs:annotation>
			<xs:documentation>Single ad cue point element</xs:documentation>
			<xs:appinfo>
				<example>
					<scene-ad-cue-point sceneId="{scene id}" entryId="{entry id}" systemName="MY_AD_CUE_POINT_SYSTEM_NAME">
						<sceneStartTime>00:00:05</sceneStartTime>
						<tags>
							<tag>sample</tag>
							<tag>my_tag</tag>
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
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePoint::getCuePointTypeCoreValue()
	 */
	public static function getCuePointTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('CuePointType', $value);
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getBaseEntryCloneOptionsCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('BaseEntryCloneOptions', $value);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePoint::getApiValue()
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePointXmlParser::parseXml()
	 */
	public static function parseXml(SimpleXMLElement $scene, $partnerId, CuePoint $cuePoint = null)
	{
		if($scene->getName() != 'scene-ad-cue-point')
			return $cuePoint;
			
		if(!$cuePoint)
			$cuePoint = vCuePointManager::parseXml($scene, $partnerId, new AdCuePoint());
			
		if(!($cuePoint instanceof AdCuePoint))
			return null;
		
		if(isset($scene->sceneEndTime))
			$cuePoint->setEndTime(vXml::timeToInteger($scene->sceneEndTime));
		if(isset($scene->sceneTitle))
			$cuePoint->setName($scene->sceneTitle);
		if(isset($scene->sourceUrl))
			$cuePoint->setSourceUrl($scene->sourceUrl);
			
		$cuePoint->setAdType($scene->adType);
		$cuePoint->setSubType($scene->protocolType);
		
		return $cuePoint;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePointXmlParser::generateXml()
	 */
	public static function generateXml(CuePoint $cuePoint, SimpleXMLElement $scenes, SimpleXMLElement $scene = null)
	{
		if(!($cuePoint instanceof AdCuePoint))
			return $scene;
			
		if(!$scene)
			$scene = vCuePointManager::generateCuePointXml($cuePoint, $scenes->addChild('scene-ad-cue-point'));
		
		if($cuePoint->getEndTime())
			$scene->addChild('sceneEndTime', vXml::integerToTime($cuePoint->getEndTime()));
		if($cuePoint->getName())
			$scene->addChild('sceneTitle', vMrssManager::stringToSafeXml($cuePoint->getName()));
		if($cuePoint->getSourceUrl())
			$scene->addChild('sourceUrl', htmlspecialchars($cuePoint->getSourceUrl()));

		$scene->addChild('adType', $cuePoint->getAdType());
		$scene->addChild('protocolType', $cuePoint->getSubType());
			
		return $scene;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePointXmlParser::syndicate()
	 */
	public static function syndicate(CuePoint $cuePoint, SimpleXMLElement $scenes, SimpleXMLElement $scene = null)
	{
		if(!($cuePoint instanceof AdCuePoint))
			return $scene;
			
		if(!$scene)
			$scene = vCuePointManager::syndicateCuePointXml($cuePoint, $scenes->addChild('scene-ad-cue-point'));
		
		if($cuePoint->getEndTime())
			$scene->addChild('sceneEndTime', vXml::integerToTime($cuePoint->getEndTime()));
		if($cuePoint->getName())
			$scene->addChild('sceneTitle', vMrssManager::stringToSafeXml($cuePoint->getName()));
		if($cuePoint->getSourceUrl())
			$scene->addChild('sourceUrl', htmlspecialchars($cuePoint->getSourceUrl()));

		$scene->addChild('adType', $cuePoint->getAdType());
		$scene->addChild('protocolType', $cuePoint->getSubType());
			
		return $scene;
	}
	
	public static function getTypesToIndexOnEntry()
	{
		return array();
	}

	public static function shouldCloneByProperty(entry $entry)
	{
		return $entry->shouldCloneByProperty(self::getBaseEntryCloneOptionsCoreValue( BaseEntryAdCuePointCloneOptions::AD_CUE_POINTS), false);
	}

	public static function getTypesToElasticIndexOnEntry()
	{
		return array();
	}
}
