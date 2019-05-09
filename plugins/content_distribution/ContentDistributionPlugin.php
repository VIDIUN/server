<?php
/**
 * @package plugins.contentDistribution
 */
class ContentDistributionPlugin extends VidiunPlugin implements IVidiunPermissions, IVidiunServices, IVidiunEventConsumers, IVidiunEnumerator, IVidiunVersion, IVidiunSearchDataContributor, IVidiunObjectLoader, IVidiunAdminConsolePages, IVidiunApplicationPartialView, IVidiunPending, IVidiunSchemaContributor
{
	const PLUGIN_NAME = 'contentDistribution';
	const PLUGIN_VERSION_MAJOR = 2;
	const PLUGIN_VERSION_MINOR = 0;
	const PLUGIN_VERSION_BUILD = 0;
	const CONTENT_DSTRIBUTION_MANAGER = 'vContentDistributionFlowManager';
	const CONTENT_DSTRIBUTION_COPY_HANDLER = 'vContentDistributionObjectCopiedHandler';
	const SPHINX_EXPANDER_FIELD_DATA = 'data';

	/* (non-PHPdoc)
	 * @see VidiunPlugin::getInstance()
	 */
	public function getInstance($interface)
	{
		if($this instanceof $interface)
			return $this;
			
		if($interface == 'IVidiunMrssContributor')
			return vContentDistributionMrssManager::get();
			
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
		$dependency = new VidiunDependency(MetadataPlugin::getPluginName());
		return array($dependency);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		if($partnerId == Partner::ADMIN_CONSOLE_PARTNER_ID)
			return true;
			
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if(!$partner)
			return false;
			
		return $partner->getPluginEnabled(self::PLUGIN_NAME);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunServices::getServicesMap()
	 */
	public static function getServicesMap()
	{
		$map = array(
			'distributionProfile' => 'DistributionProfileService',
			'entryDistribution' => 'EntryDistributionService',
			'distributionProvider' => 'DistributionProviderService',
			'genericDistributionProvider' => 'GenericDistributionProviderService',
			'genericDistributionProviderAction' => 'GenericDistributionProviderActionService',
			'contentDistributionBatch' => 'ContentDistributionBatchService',
		);
		return $map;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(
			self::CONTENT_DSTRIBUTION_MANAGER,
			self::CONTENT_DSTRIBUTION_COPY_HANDLER,
		);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('ContentDistributionBatchJobType', 'ContentDistributionFileSyncObjectType', 'ContentDistributionBatchJobObjectType', 'ContentDistributionObjectFeatureType');
	
		if($baseEnumName == 'BatchJobType')
			return array('ContentDistributionBatchJobType');
			
		if($baseEnumName == 'FileSyncObjectType')
			return array('ContentDistributionFileSyncObjectType');
		
		if($baseEnumName == 'BatchJobObjectType')
			return array('ContentDistributionBatchJobObjectType');
			
		if($baseEnumName == 'ObjectFeatureType')
			return array('ContentDistributionObjectFeatureType');
			
		return array();
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
	 * @see IVidiunSearchDataContributor::getSearchData()
	 */
	public static function getSearchData(BaseObject $object)
	{
		if(class_exists('ContentDistributionSphinxPlugin'))
			if($object instanceof entry)
				return array (ContentDistributionSphinxPlugin::getSphinxFieldName(self::SPHINX_EXPANDER_FIELD_DATA) => vContentDistributionManager::getEntrySearchValues($object));

		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'ISyncableFile' && isset($constructorArgs['objectId']))
		{
			$objectId = $constructorArgs['objectId'];

			if($enumValue == self::getContentDistributionFileSyncObjectTypeCoreValue(ContentDistributionFileSyncObjectType::GENERIC_DISTRIBUTION_ACTION))
			{
				GenericDistributionProviderActionPeer::setUseCriteriaFilter(false);
				$object = GenericDistributionProviderActionPeer::retrieveByPK($objectId);
				GenericDistributionProviderActionPeer::setUseCriteriaFilter(true);
				return $object;
			}

			if($enumValue == self::getContentDistributionFileSyncObjectTypeCoreValue(ContentDistributionFileSyncObjectType::ENTRY_DISTRIBUTION))
			{
				EntryDistributionPeer::setUseCriteriaFilter(false);
				$object = EntryDistributionPeer::retrieveByPK($objectId);
				EntryDistributionPeer::setUseCriteriaFilter(true);
				return $object;
			}

			if($enumValue == self::getContentDistributionFileSyncObjectTypeCoreValue(ContentDistributionFileSyncObjectType::DISTRIBUTION_PROFILE))
			{
				DistributionProfilePeer::setUseCriteriaFilter(false);
				$object = DistributionProfilePeer::retrieveByPK($objectId);
				DistributionProfilePeer::setUseCriteriaFilter(true);
				return $object;
			}
		}
		
		if($baseClass == 'vJobData')
		{
			if($enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_SUBMIT))
				return new vDistributionSubmitJobData();
				
			if($enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_UPDATE))
				return new vDistributionUpdateJobData();
				
			if($enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DELETE))
				return new vDistributionDeleteJobData();
				
			if($enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_FETCH_REPORT))
				return new vDistributionFetchReportJobData();
				
			if($enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_ENABLE))
				return new vDistributionEnableJobData();
				
			if($enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DISABLE))
				return new vDistributionDisableJobData();
		}
	
		if($baseClass == 'VidiunJobData')
		{
			if($enumValue == self::getApiValue(ContentDistributionBatchJobType::DISTRIBUTION_SUBMIT) || $enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_SUBMIT))
				return new VidiunDistributionSubmitJobData();
				
			if($enumValue == self::getApiValue(ContentDistributionBatchJobType::DISTRIBUTION_UPDATE) || $enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_UPDATE))
				return new VidiunDistributionUpdateJobData();
				
			if($enumValue == self::getApiValue(ContentDistributionBatchJobType::DISTRIBUTION_DELETE) || $enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DELETE))
				return new VidiunDistributionDeleteJobData();
				
			if($enumValue == self::getApiValue(ContentDistributionBatchJobType::DISTRIBUTION_FETCH_REPORT) || $enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_FETCH_REPORT))
				return new VidiunDistributionFetchReportJobData();
				
			if($enumValue == self::getApiValue(ContentDistributionBatchJobType::DISTRIBUTION_ENABLE) || $enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_ENABLE))
				return new VidiunDistributionEnableJobData();
				
			if($enumValue == self::getApiValue(ContentDistributionBatchJobType::DISTRIBUTION_DISABLE) || $enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DISABLE))
				return new VidiunDistributionDisableJobData();
		}
		
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'ISyncableFile')
		{
			if($enumValue == self::getContentDistributionFileSyncObjectTypeCoreValue(ContentDistributionFileSyncObjectType::GENERIC_DISTRIBUTION_ACTION))
				return 'GenericDistributionProviderAction';
			if($enumValue == self::getContentDistributionFileSyncObjectTypeCoreValue(ContentDistributionFileSyncObjectType::ENTRY_DISTRIBUTION))
				return 'EntryDistribution';
			if($enumValue == self::getContentDistributionFileSyncObjectTypeCoreValue(ContentDistributionFileSyncObjectType::DISTRIBUTION_PROFILE))
				return 'DistributionProfile';
		}
		
		if($baseClass == 'vJobData')
		{
			if($enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_SUBMIT))
				return 'vDistributionSubmitJobData';
				
			if($enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_UPDATE))
				return 'vDistributionUpdateJobData';
				
			if($enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DELETE))
				return 'vDistributionDeleteJobData';
				
			if($enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_FETCH_REPORT))
				return 'vDistributionFetchReportJobData';
				
			if($enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_ENABLE))
				return 'vDistributionEnableJobData';
				
			if($enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DISABLE))
				return 'vDistributionDisableJobData';
		}
	
		if($baseClass == 'VidiunJobData')
		{
			if($enumValue == self::getApiValue(ContentDistributionBatchJobType::DISTRIBUTION_SUBMIT) || $enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_SUBMIT))
				return 'VidiunDistributionSubmitJobData';
				
			if($enumValue == self::getApiValue(ContentDistributionBatchJobType::DISTRIBUTION_UPDATE) || $enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_UPDATE))
				return 'VidiunDistributionUpdateJobData';
				
			if($enumValue == self::getApiValue(ContentDistributionBatchJobType::DISTRIBUTION_DELETE) || $enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DELETE))
				return 'VidiunDistributionDeleteJobData';
				
			if($enumValue == self::getApiValue(ContentDistributionBatchJobType::DISTRIBUTION_FETCH_REPORT) || $enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_FETCH_REPORT))
				return 'VidiunDistributionFetchReportJobData';
				
			if($enumValue == self::getApiValue(ContentDistributionBatchJobType::DISTRIBUTION_ENABLE) || $enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_ENABLE))
				return 'VidiunDistributionEnableJobData';
				
			if($enumValue == self::getApiValue(ContentDistributionBatchJobType::DISTRIBUTION_DISABLE) || $enumValue == self::getBatchJobTypeCoreValue(ContentDistributionBatchJobType::DISTRIBUTION_DISABLE))
				return 'VidiunDistributionDisableJobData';
		}
		
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunAdminConsolePages::getApplicationPages()
	 */
	public static function getApplicationPages()
	{
		$pages = array();
		
		$pages[] = new DistributionProfileListAction();
		$pages[] = new DistributionProfileConfigureAction();
		$pages[] = new DistributionProfileUpdateStatusAction();

		$pages[] = new GenericDistributionProvidersListAction();
		$pages[] = new GenericDistributionProviderConfigureAction();
		$pages[] = new GenericDistributionProviderDeleteAction();

        $pages[] = new XsltTesterAction();
        $pages[] = new XsltTesterApiAction();

		return $pages;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunApplicationPartialView::getApplicationPartialViews()
	 */
	public static function getApplicationPartialViews($controller, $action)
	{
		if($controller == 'batch' && $action == 'entryInvestigation')
		{
			return array(
				new Vidiun_View_Helper_EntryInvestigateDistribution(),
			);
		}
		
		return array();
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getContentDistributionFileSyncObjectTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('FileSyncObjectType', $value);
	}
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getBatchJobTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('BatchJobType', $value);
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
	 * @see IVidiunSchemaContributor::contributeToSchema()
	 */
	public static function contributeToSchema($type)
	{
		$coreType = vPluginableEnumsManager::apiToCore('SchemaType', $type);
		if($coreType != SchemaType::SYNDICATION)
			return null;
			
		$xsd = '
		
	<!-- ' . self::getPluginName() . ' -->
	
	<xs:complexType name="T_distribution">
		<xs:sequence>
			<xs:element name="remoteId" minOccurs="0" maxOccurs="1" type="xs:string">
				<xs:annotation>
					<xs:documentation>Unique id in the remote site</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="sunrise" minOccurs="0" maxOccurs="1" type="xs:int">
				<xs:annotation>
					<xs:documentation>Time that the entry will become available in the remote site</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="sunset" minOccurs="0" maxOccurs="1" type="xs:int">
				<xs:annotation>
					<xs:documentation>Time that the entry will become unavailable in the remote site</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="flavorAssetIds" minOccurs="0" maxOccurs="1">
				<xs:annotation>
					<xs:documentation>Ids of flavor assets to be submitted to the remote site</xs:documentation>
				</xs:annotation>
				<xs:complexType>
					<xs:sequence>
						<xs:element name="flavorAssetId" minOccurs="0" maxOccurs="unbounded" type="xs:string" />
					</xs:sequence>
				</xs:complexType>
			</xs:element>
			<xs:element name="thumbAssetIds" minOccurs="0" maxOccurs="1">
				<xs:annotation>
					<xs:documentation>Ids of thumbnail assets to be submitted to the remote site</xs:documentation>
				</xs:annotation>
				<xs:complexType>
					<xs:sequence>
						<xs:element name="thumbAssetId" minOccurs="0" maxOccurs="unbounded" type="xs:string" />
					</xs:sequence>
				</xs:complexType>
			</xs:element>
			<xs:element name="assetIds" minOccurs="0" maxOccurs="1">
				<xs:annotation>
					<xs:documentation>Ids of assets to be submitted to the remote site</xs:documentation>
				</xs:annotation>
				<xs:complexType>
					<xs:sequence>
						<xs:element name="assetId" minOccurs="0" maxOccurs="unbounded" type="xs:string" />
					</xs:sequence>
				</xs:complexType>
			</xs:element>
			<xs:element name="errorDescription" minOccurs="0" maxOccurs="1" type="xs:string">
				<xs:annotation>
					<xs:documentation>Submission error description</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="createdAt" minOccurs="1" maxOccurs="1" type="xs:dateTime">
				<xs:annotation>
					<xs:documentation>Entry distribution creation date</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="updatedAt" minOccurs="1" maxOccurs="1" type="xs:dateTime">
				<xs:annotation>
					<xs:documentation>Entry distribution last update date</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="submittedAt" minOccurs="0" maxOccurs="1" type="xs:dateTime">
				<xs:annotation>
					<xs:documentation>Entry distribution submission date</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="lastReport" minOccurs="0" maxOccurs="1" type="xs:dateTime">
				<xs:annotation>
					<xs:documentation>Entry distribution last report date</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="dirtyStatus" minOccurs="0" maxOccurs="1" type="VidiunEntryDistributionFlag">
				<xs:annotation>
					<xs:documentation>Indicates that there are future action to be submitted</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="status" minOccurs="1" maxOccurs="1" type="VidiunEntryDistributionStatus">
				<xs:annotation>
					<xs:documentation>Entry distribution status</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="sunStatus" minOccurs="1" maxOccurs="1" type="VidiunEntryDistributionSunStatus">
				<xs:annotation>
					<xs:documentation>Entry distribution availability status</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="plays" minOccurs="0" maxOccurs="1" type="xs:int">
				<xs:annotation>
					<xs:documentation>Entry plays count in the remote site</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="views" minOccurs="0" maxOccurs="1" type="xs:int">
				<xs:annotation>
					<xs:documentation>Entry views count in the remote site</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="errorNumber" minOccurs="0" maxOccurs="1" type="xs:int">
				<xs:annotation>
					<xs:documentation>Submission error number</xs:documentation>
				</xs:annotation>
			</xs:element>
			<xs:element name="errorType" minOccurs="0" maxOccurs="1" type="VidiunBatchJobErrorTypes">
				<xs:annotation>
					<xs:documentation>Submission error type</xs:documentation>
				</xs:annotation>
			</xs:element>
		
			<xs:element ref="distribution-extension" minOccurs="0" maxOccurs="unbounded" />
			
		</xs:sequence>
		
		<xs:attribute name="entryDistributionId" use="required" type="xs:int">
			<xs:annotation>
				<xs:documentation>Entry distribution unique id</xs:documentation>
			</xs:annotation>
		</xs:attribute>
		<xs:attribute name="provider" use="optional" type="xs:string">
			<xs:annotation>
				<xs:documentation>Entry distribution provider</xs:documentation>
			</xs:annotation>
		</xs:attribute>
		<xs:attribute name="distributionProviderId" use="optional" type="xs:int">
			<xs:annotation>
				<xs:documentation>Entry distribution provider id<br/>relevant to generic providers</xs:documentation>
			</xs:annotation>
		</xs:attribute>
		<xs:attribute name="feedId" use="optional" type="xs:string">
			<xs:annotation>
				<xs:documentation>Entry distribution feed id<br/>relevant to syndicated providers</xs:documentation>
			</xs:annotation>
		</xs:attribute>
		<xs:attribute name="distributionProfileId" use="required" type="xs:int">
			<xs:annotation>
				<xs:documentation>Entry distribution profile id</xs:documentation>
			</xs:annotation>
		</xs:attribute>
		<xs:attribute name="distributionProfile" use="optional" type="xs:string">
			<xs:annotation>
				<xs:documentation>Entry distribution profile system name</xs:documentation>
			</xs:annotation>
		</xs:attribute>
		<xs:attribute name="distributionProfileName" use="optional" type="xs:string">
			<xs:annotation>
				<xs:documentation>Entry distribution profile name</xs:documentation>
			</xs:annotation>
		</xs:attribute>
		
	</xs:complexType>
	
	<xs:element name="distribution" type="T_distribution" substitutionGroup="item-extension">
		<xs:annotation>
			<xs:documentation>Entry distribution element</xs:documentation>
			<xs:appinfo>
				<example>
					<distribution	entryDistributionId="{entry distribution id}" 
									distributionProfileId="{distribution profile id}" 
									distributionProfileName="My Profile"
					>
						<remoteId>{remote site id}</remoteId>
						<sunrise>1305636600</sunrise>
						<sunset>1305640200</sunset>
						<flavorAssetIds>
							<flavorAssetId>0_bp1qzu1d</flavorAssetId>
							<flavorAssetId>0_bp1qzfsd</flavorAssetId>
						</flavorAssetIds>
						<thumbAssetIds>
							<thumbAssetId>0_di94zu1d</thumbAssetId>
							<thumbAssetId>0_di940sde</thumbAssetId>
						</thumbAssetIds>
						<errorDescription>Error: No metadata objects found</errorDescription>
						<createdAt>2011-05-17T07:46:20</createdAt>
						<updatedAt>2011-06-09T09:23:46</updatedAt>
						<submittedAt>2011-05-17T08:03:00</submittedAt>
						<dirtyStatus>3</dirtyStatus>
						<status>8</status>
						<sunStatus>3</sunStatus>
						<errorType>1</errorType>
					</distribution>
				</example>
			</xs:appinfo>
		</xs:annotation>
	</xs:element>
	<xs:element name="distribution-extension" />
		';
		
		return $xsd;
	}
}
