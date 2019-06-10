<?php
/**
 * Enable time based cue point objects management on entry objects
 * @package plugins.cuePoint
 */
class CuePointPlugin extends VidiunPlugin implements IVidiunServices, IVidiunPermissions, IVidiunEventConsumers, IVidiunVersion, IVidiunEnumerator, IVidiunSchemaContributor, IVidiunSchemaDefiner, IVidiunMrssContributor, IVidiunSearchDataContributor, IVidiunElasticSearchDataContributor
{
	const PLUGIN_NAME = 'cuePoint';
	const PLUGIN_VERSION_MAJOR = 1;
	const PLUGIN_VERSION_MINOR = 0;
	const PLUGIN_VERSION_BUILD = 0;
	const CUE_POINT_MANAGER = 'vCuePointManager';
	const SEARCH_FIELD_DATA = 'data';
	const SEARCH_TEXT_SUFFIX = 'cpend';
	const ENTRY_CUE_POINT_INDEX_PREFIX = 'cps_';
	const ENTRY_CUE_POINT_INDEX_SUFFIX = 'cpe_';
	const ENTRY_CUE_POINT_INDEX_SUB_TYPE = 'cpst';
	
	const CUE_POINT_FETCH_LIMIT = 1000;
	
	
	/* (non-PHPdoc)
	 * @see IVidiunPlugin::getPluginName()
	 */
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunVersion::getVersion()
	 */
	public static function getVersion()
	{
		return new VidiunVersion(
			self::PLUGIN_VERSION_MAJOR,
			self::PLUGIN_VERSION_MINOR,
			self::PLUGIN_VERSION_BUILD
		);
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
			'cuePoint' => 'CuePointService',
			'liveCuePoint' => 'LiveCuePointService',
		);
		return $map;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(
			self::CUE_POINT_MANAGER,
		);
	}

	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('CuePointSchemaType', 'CuePointObjectFeatureType');
		
		if($baseEnumName == 'SchemaType')
			return array('CuePointSchemaType');
			
		if($baseEnumName == 'ObjectFeatureType')
			return array('CuePointObjectFeatureType');
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSchemaContributor::contributeToSchema()
	 */
	public static function contributeToSchema($type)
	{
		$coreType = vPluginableEnumsManager::apiToCore('SchemaType', $type);
		if($coreType != SchemaType::SYNDICATION)
			return null;
			
		
		$xsd = '
		
	<!-- ' . self::getPluginName() . ' -->
	
	<xs:complexType name="T_scenes">
		<xs:sequence>
			<xs:element ref="scene" minOccurs="1" maxOccurs="unbounded">
				<xs:annotation>
					<xs:documentation>Cue point element</xs:documentation>
				</xs:annotation>
			</xs:element>
		</xs:sequence>
	</xs:complexType>	
	
	<xs:complexType name="T_scene" abstract="true">
		<xs:sequence>
			<xs:element name="sceneStartTime" minOccurs="1" maxOccurs="1" type="xs:time">
				<xs:annotation>
					<xs:documentation>Cue point start time</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="createdAt" minOccurs="1" maxOccurs="1" type="xs:dateTime">
				<xs:annotation>
					<xs:documentation>Cue point creation date</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="updatedAt" minOccurs="1" maxOccurs="1" type="xs:dateTime">
				<xs:annotation>
					<xs:documentation>Cue point last update date</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="userId" minOccurs="0" maxOccurs="1" type="xs:string">
				<xs:annotation>
					<xs:documentation>Cue point owner user id</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element ref="tags" minOccurs="0" maxOccurs="1">
				<xs:annotation>
					<xs:documentation>Cue point searchable keywords</xs:documentation>
				</xs:annotation>
			</xs:element>
		</xs:sequence>
		
		<xs:attribute name="sceneId" use="required">
			<xs:annotation>
				<xs:documentation>ID of cue point to apply update/delete action on</xs:documentation>
			</xs:annotation>
			<xs:simpleType>
				<xs:restriction base="xs:string">
					<xs:maxLength value="250"/>
				</xs:restriction>
			</xs:simpleType>
		</xs:attribute>
		<xs:attribute name="systemName" use="optional">
			<xs:annotation>
				<xs:documentation>System name of cue point to apply update/delete action on</xs:documentation>
			</xs:annotation>
			<xs:simpleType>
				<xs:restriction base="xs:string">
					<xs:maxLength value="120"/>
				</xs:restriction>
			</xs:simpleType>
		</xs:attribute>
		
	</xs:complexType>
	
	<xs:element name="scenes" type="T_scenes" substitutionGroup="item-extension">
		<xs:annotation>
			<xs:documentation>Cue points wrapper</xs:documentation>
			<xs:appinfo>
				<example>
					<scenes>
						<scene-ad-cue-point entryId="{entry id}" systemName="MY_AD_CUE_POINT_SYSTEM_NAME">...</scene-ad-cue-point>
						<scene-annotation entryId="{entry id}" systemName="MY_ANNOTATION_PARENT_SYSTEM_NAME">...</scene-annotation>
						<scene-annotation entryId="{entry id}">...</scene-annotation>
						<scene-code-cue-point entryId="{entry id}">...</scene-code-cue-point>
						<scene-thumb-cue-point entryId="{entry id}">...</scene-thumb-cue-point>
					</scenes>
				</example>
			</xs:appinfo>
		</xs:annotation>
	</xs:element>
	
	<xs:element name="scene" type="T_scene">
		<xs:annotation>
			<xs:documentation>
				Base cue point element<br/>
				Is abstract and cannot be used<br/>
				Use the extended elements only
			</xs:documentation>
		</xs:annotation>
	</xs:element>
	
	<xs:element name="scene-extension" />
		';
		
		return $xsd;
	}

	/* (non-PHPdoc)
	 * @see IVidiunMrssContributor::contribute()
	 */
	public function contribute(BaseObject $object, SimpleXMLElement $mrss, vMrssParameters $mrssParams = null)
	{
		if(!($object instanceof entry))
			return;
		
		$cuePoints = CuePointPeer::retrieveByEntryId($object->getId());
		if(!count($cuePoints))
			return;
		
		$scenes = $mrss->addChild('scenes');
		vCuePointManager::syndicate($cuePoints, $scenes);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSchemaContributor::contributeToSchema()
	 */
	public static function getPluginSchema($type)
	{
		$coreType = vPluginableEnumsManager::apiToCore('SchemaType', $type);
		
		if($coreType == self::getSchemaTypeCoreValue(CuePointSchemaType::INGEST_API))
			return new SimpleXMLElement(file_get_contents(dirname(__FILE__) . '/xml/ingestion.xsd'));
			
		if($coreType == self::getSchemaTypeCoreValue(CuePointSchemaType::SERVE_API))
			return new SimpleXMLElement(file_get_contents(dirname(__FILE__) . '/xml/serve.xsd'));
			
		return null;
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
	public static function getObjectFeatureTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('ObjectFeatureType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunMrssContributor::getObjectFeatureType()
	 */
	public function getObjectFeatureType ()
	{
		return self::getObjectFeatureTypeCoreValue(CuePointObjectFeatureType::CUE_POINT);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSearchDataContributor::getSearchData()
	 */
	public static function getSearchData(BaseObject $object)
	{
		if($object instanceof entry && self::isAllowedPartner($object->getPartnerId()))
			return self::getCuePointSearchData($object);
			
		return null;
	}
	
	public static function getCuePointSearchData(entry $entry)
	{
		$indexOnEntryTypes = self::getIndexOnEntryTypes();
		if(!count($indexOnEntryTypes))
			return;
		
		$offset = 0;
		$contributedDataSize = 0;
		$searchDataBytType = array();
		$entryId = $entry->getId();
		$partnerId = $entry->getPartnerId();
		
		do 
		{
			CuePointPeer::setUseCriteriaFilter(false);
			$cuePointObjects = CuePointPeer::retrieveByEntryIdTypeAndLimit($partnerId, $entryId, self::CUE_POINT_FETCH_LIMIT, $offset, $indexOnEntryTypes);
			CuePointPeer::setUseCriteriaFilter(true);
			
			foreach($cuePointObjects as $cuePoint)
			{
				/* @var $cuePoint CuePoint */
				$contributedData = $cuePoint->contributeData();
				
				if(!$contributedData)
					continue;
				
				$cuePointType = $cuePoint->getType();
				
				$contributedData = self::buildDataToIndexOnEntry($contributedData, $cuePointType, $cuePoint->getPartnerId(), $cuePoint->getId(), $cuePoint->getSubType());
				
				if(!isset($searchDataBytType[$cuePointType]))
					$searchDataBytType[$cuePointType] = '';
				
				$searchDataBytType[$cuePointType] .= $contributedData . ' ';
				$contributedDataSize += strlen($contributedData) + 1; // +1 for the ' '
			}
			
			$handledObjectsCount = count($cuePointObjects);
			$offset += $handledObjectsCount;
		} 
		while ($handledObjectsCount == self::CUE_POINT_FETCH_LIMIT && //In case cue point was deleted during index execution than offset will not reach count so break when count is 0
					$contributedDataSize < vSphinxSearchManager::MAX_SIZE_FOR_PLUGIN_SEARCH_DATA);
		
		
		$dataField  = CuePointPlugin::getSearchFieldName(CuePointPlugin::SEARCH_FIELD_DATA);
		$searchValues = array(
			$dataField => CuePointPlugin::PLUGIN_NAME . "_" . $entry->getPartnerId() . ' ' . implode(' ', $searchDataBytType) . ' ' . CuePointPlugin::SEARCH_TEXT_SUFFIX
		);
		
		return $searchValues;
	}
	
	public static function buildDataToIndexOnEntry($contributedData, $type, $partnerId, $cuePointId, $subType = null)
	{	
		$prefix = self::ENTRY_CUE_POINT_INDEX_PREFIX . $partnerId . "_" . $type;
		
		if($subType)
			$prefix .= " " . self::ENTRY_CUE_POINT_INDEX_SUB_TYPE . $subType;
		
		$suffix = self::ENTRY_CUE_POINT_INDEX_SUFFIX . $partnerId . "_" . $type;
			
		return $cuePointId . " " . $prefix . " " . $contributedData . $suffix;
	}
	
	public static function getIndexOnEntryTypes()
	{
		$indexOnEntryTypes = array();
		
		$pluginInstances = VidiunPluginManager::getPluginInstances('IVidiunCuePoint');
		foreach ($pluginInstances as $pluginInstance)
		{
			$currIndexOnEntryTypes = $pluginInstance::getTypesToIndexOnEntry();
			
			$indexOnEntryTypes = array_merge($indexOnEntryTypes, $currIndexOnEntryTypes);
		}
		
		return $indexOnEntryTypes;
	}
	
	/**
	 * return field name as appears in index schema
	 * @param string $fieldName
	 */
	public static function getSearchFieldName($fieldName){
		if ($fieldName == self::SEARCH_FIELD_DATA)
			return  'plugins_data';
			
		return CuePointPlugin::getPluginName() . '_' . $fieldName;
	}

	/**
	 * Return search data to be associated with the object
	 *
	 * @param BaseObject $object
	 * @return ArrayObject
	 */
	public static function getElasticSearchData(BaseObject $object)
	{
		if($object instanceof entry && self::isAllowedPartner($object->getPartnerId()))
			return self::getCuePointElasticSearchData($object);

		return null;
	}

	public static function getCuePointElasticSearchData(entry $entry)
	{
		$indexOnEntryTypes = self::getElasticIndexOnEntryTypes();

		if(!count($indexOnEntryTypes))
			return;

		$offset = 0;
		$entryId = $entry->getId();
		$partnerId = $entry->getPartnerId();
		$data = array();
		$cuePoints = null;
		do
		{
			CuePointPeer::setUseCriteriaFilter(false);
			$cuePointObjects = CuePointPeer::retrieveByEntryIdTypeAndLimit($partnerId, $entryId, self::CUE_POINT_FETCH_LIMIT, $offset, $indexOnEntryTypes);
			CuePointPeer::setUseCriteriaFilter(true);

			foreach($cuePointObjects as $cuePoint)
			{
				/* @var $cuePoint CuePoint */
				$contributedData = $cuePoint->contributeElasticData();
				if(!$contributedData)
					continue;

				if (isset($contributedData['cue_point_text']) && (strlen($contributedData['cue_point_text']) > vElasticSearchManager::MAX_LENGTH))
					$contributedData['cue_point_text'] = substr($contributedData['cue_point_text'], 0, vElasticSearchManager::MAX_LENGTH);

				$cuePointData = $contributedData;
				$cuePointData['cue_point_type'] = $cuePoint->getType();
				$cuePointData['cue_point_id'] = $cuePoint->getId();
				if(!is_null($cuePoint->getStartTime()))
					$cuePointData['cue_point_start_time'] = $cuePoint->getStartTime();
				if(!is_null($cuePoint->getEndTime()))
					$cuePointData['cue_point_end_time'] = $cuePoint->getEndTime();
				//add cue point metadata - todo maybe add checkbox
//				$metaDataPlugin = VidiunPluginManager::getPluginInstance(CuePointMetadataPlugin::PLUGIN_NAME);
//				if($metaDataPlugin)
//				{
//					$cuePointElasticMetaData = $metaDataPlugin::getElasticSearchData($cuePoint);
//
//					if($cuePointElasticMetaData && count($cuePointElasticMetaData[vMetadataManager::ELASTIC_DATA_FIELD_NAME]))
//					{
//						foreach ($cuePointElasticMetaData[vMetadataManager::ELASTIC_DATA_FIELD_NAME] as $fieldName => $fieldValue)
//						{
//							$cuePointData['cue_point_metadata'][$fieldName] = $fieldValue;
//						}
//					}
//				}
				$data[] = $cuePointData;
			}

			$handledObjectsCount = count($cuePointObjects);
			$offset += $handledObjectsCount;
		}
		while ($handledObjectsCount == self::CUE_POINT_FETCH_LIMIT &&
			$offset < vElasticSearchManager::MAX_CUE_POINTS); //remove after we move to php7

		if(count($data))
			$cuePoints['cue_points'] = $data;

		return $cuePoints;
	}

	public static function getElasticIndexOnEntryTypes()
	{
		$indexOnEntryTypes = array();
		$pluginInstances = VidiunPluginManager::getPluginInstances('IVidiunCuePoint');
		foreach ($pluginInstances as $pluginInstance)
		{
			$currIndexOnEntryTypes = $pluginInstance::getTypesToElasticIndexOnEntry();

			$indexOnEntryTypes = array_merge($indexOnEntryTypes, $currIndexOnEntryTypes);
		}

		return $indexOnEntryTypes;
	}

}
