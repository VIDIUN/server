<?php
/**
 * Enable annotation cue point objects management on entry objects
 * @package plugins.annotation
 */
class AnnotationPlugin extends BaseCuePointPlugin implements IVidiunServices, IVidiunCuePoint, IVidiunCuePointXmlParser
{
	const PLUGIN_NAME = 'annotation';
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
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap()
	{
		$map = array(
			'annotation' => 'AnnotationService',
		);
		return $map;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('AnnotationCuePointType', 'BaseEntryAnnotationCuePointCloneOptions');
	
		if($baseEnumName == 'CuePointType')
			return array('AnnotationCuePointType');

		if($baseEnumName == 'BaseEntryCloneOptions')
			return array('BaseEntryAnnotationCuePointCloneOptions');
			
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
		if($baseClass == 'VidiunCuePoint' && $enumValue == self::getCuePointTypeCoreValue(AnnotationCuePointType::ANNOTATION))
			return new VidiunAnnotation();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'CuePoint' && $enumValue == self::getCuePointTypeCoreValue(AnnotationCuePointType::ANNOTATION))
			return 'Annotation';
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSchemaContributor::contributeToSchema()
	 */
	public static function contributeToSchema($type)
	{
		$coreType = vPluginableEnumsManager::apiToCore('SchemaType', $type);
		
		if(
			$coreType == SchemaType::SYNDICATION
			||
			$coreType == CuePointPlugin::getSchemaTypeCoreValue(CuePointSchemaType::SERVE_API)
		)
			return '
		
	<!-- ' . self::getPluginName() . ' -->
	
	<xs:complexType name="T_scene_annotation">
		<xs:complexContent>
			<xs:extension base="T_scene">
				<xs:sequence>
					<xs:element name="sceneEndTime" minOccurs="1" maxOccurs="1" type="xs:time">
						<xs:annotation>
							<xs:documentation>Cue point end time</xs:documentation>
						</xs:annotation>
					</xs:element>
					<xs:element name="sceneText" minOccurs="0" maxOccurs="1" type="xs:string">
						<xs:annotation>
							<xs:documentation>Free text description</xs:documentation>
						</xs:annotation>
					</xs:element>
					<xs:element name="parent" minOccurs="0" maxOccurs="1">
						<xs:annotation>
							<xs:documentation>System name of the parent annotation</xs:documentation>
						</xs:annotation>
						<xs:simpleType>
							<xs:restriction base="xs:string">
								<xs:maxLength value="120"/>
							</xs:restriction>
						</xs:simpleType>
					</xs:element>
					<xs:element name="parentId" minOccurs="0" maxOccurs="1">
						<xs:annotation>
							<xs:documentation>ID of the parent annotation</xs:documentation>
						</xs:annotation>
						<xs:simpleType>
							<xs:restriction base="xs:string">
								<xs:maxLength value="250"/>
							</xs:restriction>
						</xs:simpleType>
					</xs:element>
					
					<xs:element ref="scene-extension" minOccurs="0" maxOccurs="unbounded" />
				</xs:sequence>
			</xs:extension>
		</xs:complexContent>
	</xs:complexType>
	
	<xs:element name="scene-annotation" type="T_scene_annotation" substitutionGroup="scene">
		<xs:annotation>
			<xs:documentation>Single annotation element</xs:documentation>
			<xs:appinfo>
				<example>
					<scene-annotation sceneId="{scene id}" entryId="{entry id}">
						<sceneStartTime>00:00:05.3</sceneStartTime>
						<tags>
							<tag>sample</tag>
							<tag>my_tag</tag>
						</tags>
						<sceneEndTime>00:00:10</sceneEndTime>
						<sceneText>my annotation</sceneText>
					</scene-annotation>
				</example>
			</xs:appinfo>
		</xs:annotation>
	</xs:element>
		';
		
		if($coreType == CuePointPlugin::getSchemaTypeCoreValue(CuePointSchemaType::INGEST_API))
			return '
		
	<!-- ' . self::getPluginName() . ' -->
	
	<xs:complexType name="T_scene_annotation">
		<xs:complexContent>
			<xs:extension base="T_scene">
				<xs:sequence>
					<xs:element name="sceneEndTime" minOccurs="1" maxOccurs="1" type="xs:time">
						<xs:annotation>
							<xs:documentation>Cue point end time</xs:documentation>
						</xs:annotation>
					</xs:element>
					<xs:element name="sceneText" minOccurs="0" maxOccurs="1" type="xs:string">
						<xs:annotation>
							<xs:documentation>Free text description</xs:documentation>
						</xs:annotation>
					</xs:element>
					<xs:choice minOccurs="0" maxOccurs="1">
						<xs:element name="parent" minOccurs="1" maxOccurs="1">
							<xs:annotation>
								<xs:documentation>System name of the parent annotation</xs:documentation>
							</xs:annotation>
							<xs:simpleType>
								<xs:restriction base="xs:string">
									<xs:maxLength value="120"/>
								</xs:restriction>
							</xs:simpleType>
						</xs:element>
						<xs:element name="parentId" minOccurs="1" maxOccurs="1">
							<xs:annotation>
								<xs:documentation>ID of the parent annotation</xs:documentation>
							</xs:annotation>
							<xs:simpleType>
								<xs:restriction base="xs:string">
									<xs:maxLength value="250"/>
								</xs:restriction>
							</xs:simpleType>
						</xs:element>
					</xs:choice>
					
					<xs:element ref="scene-extension" minOccurs="0" maxOccurs="unbounded" />
				</xs:sequence>
			</xs:extension>
		</xs:complexContent>
	</xs:complexType>
	
	<xs:element name="scene-annotation" type="T_scene_annotation" substitutionGroup="scene">
		<xs:annotation>
			<xs:documentation>Single annotation element</xs:documentation>
			<xs:appinfo>
				<example title="Single annotation example">
					<scene-annotation entryId="{entry id}">
						<sceneStartTime>00:00:05.3</sceneStartTime>
						<tags>
							<tag>sample</tag>
							<tag>my_tag</tag>
						</tags>
						<sceneEndTime>00:00:10</sceneEndTime>
						<sceneText>my annotation</sceneText>
					</scene-annotation>
				</example>
				<example title="Multiple related annotations example">
					<scene-annotation entryId="{entry id}" systemName="MY_ANNOTATION_PARENT_SYSTEM_NAME">
						<sceneStartTime>00:00:05.3</sceneStartTime>
						<tags>
							<tag>sample</tag>
							<tag>my_tag</tag>
						</tags>
						<sceneEndTime>00:00:10</sceneEndTime>
						<sceneText>my annotation parent</sceneText>
					</scene-annotation>
					<scene-annotation entryId="{entry id}">
						<sceneStartTime>00:00:05.3</sceneStartTime>
						<tags>
							<tag>sample</tag>
							<tag>my_tag</tag>
						</tags>
						<sceneEndTime>00:00:10</sceneEndTime>
						<sceneText>my annotation child</sceneText>
						<parent>MY_ANNOTATION_PARENT_SYSTEM_NAME</parent>
					</scene-annotation>
				</example>
			</xs:appinfo>
		</xs:annotation>
	</xs:element>
		';
		
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePoint::getCuePointTypeCoreValue()
	 */
	public static function getCuePointTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('CuePointType', $value);
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
		if($scene->getName() != 'scene-annotation')
			return $cuePoint;
			
		if(!$cuePoint)
			$cuePoint = vCuePointManager::parseXml($scene, $partnerId, new Annotation());
			
		if(!($cuePoint instanceof Annotation))
			return null;
		
		$cuePoint->setEndTime(vXml::timeToInteger($scene->sceneEndTime));
		if(isset($scene->sceneText))
			$cuePoint->setText($scene->sceneText);
			
		$parentCuePoint = null;
		if(isset($scene->parentId))
			$parentCuePoint = CuePointPeer::retrieveByPK($scene->parentId);
		elseif(isset($scene->parent))
			$parentCuePoint = CuePointPeer::retrieveBySystemName($cuePoint->getEntryId(), $scene->parent);
		if($parentCuePoint)
			$cuePoint->setParentId($parentCuePoint->getId());
		
		return $cuePoint;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePointXmlParser::generateXml()
	 */
	public static function generateXml(CuePoint $cuePoint, SimpleXMLElement $scenes, SimpleXMLElement $scene = null)
	{
		if(!($cuePoint instanceof Annotation))
			return $scene;
			
		if(!$scene)
			$scene = vCuePointManager::generateCuePointXml($cuePoint, $scenes->addChild('scene-annotation'));
			
		$scene->addChild('sceneEndTime', vXml::integerToTime($cuePoint->getEndTime()));
		if($cuePoint->getText())
			$scene->addChild('sceneText', vMrssManager::stringToSafeXml($cuePoint->getText()));
		if($cuePoint->getParentId())
		{
			$parentCuePoint = CuePointPeer::retrieveByPK($cuePoint->getParentId());
			if($parentCuePoint)
			{
				if($parentCuePoint->getSystemName())
					$scene->addChild('parent', vMrssManager::stringToSafeXml($parentCuePoint->getSystemName()));
				$scene->addChild('parentId', $parentCuePoint->getId());
			}
		}
			
		return $scene;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunCuePointXmlParser::syndicate()
	 */
	public static function syndicate(CuePoint $cuePoint, SimpleXMLElement $scenes, SimpleXMLElement $scene = null)
	{
		if(!($cuePoint instanceof Annotation))
			return $scene;
			
		if(!$scene)
			$scene = vCuePointManager::syndicateCuePointXml($cuePoint, $scenes->addChild('scene-annotation'));
			
		$scene->addChild('sceneEndTime', vXml::integerToTime($cuePoint->getEndTime()));
		if($cuePoint->getText())
			$scene->addChild('sceneText', vMrssManager::stringToSafeXml($cuePoint->getText()));
		if($cuePoint->getParentId())
		{
			$parentCuePoint = CuePointPeer::retrieveByPK($cuePoint->getParentId());
			if($parentCuePoint)
			{
				if($parentCuePoint->getSystemName())
					$scene->addChild('parent', vMrssManager::stringToSafeXml($parentCuePoint->getSystemName()));
				$scene->addChild('parentId', $parentCuePoint->getId());
			}
		}
			
		return $scene;
	}
	
	public static function getTypesToIndexOnEntry()
	{
		return array(self::getCuePointTypeCoreValue(AnnotationCuePointType::ANNOTATION));
	}

	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getBaseEntryCloneOptionsCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('BaseEntryCloneOptions', $value);
	}

	public static function shouldCloneByProperty(entry $entry)
	{
		return $entry->shouldCloneByProperty(self::getBaseEntryCloneOptionsCoreValue( BaseEntryAnnotationCuePointCloneOptions::ANNOTATION_CUE_POINTS), false);
	}

	public static function getTypesToElasticIndexOnEntry()
	{
		return array(self::getCuePointTypeCoreValue(AnnotationCuePointType::ANNOTATION));
	}

}
