<?php
/**
 * Enable indexing and searching caption asset objects
 * @package plugins.captionSearch
 */
class CaptionSearchPlugin extends VidiunPlugin implements IVidiunPending, IVidiunPermissions, IVidiunServices, IVidiunEventConsumers, IVidiunEnumerator, IVidiunObjectLoader, IVidiunSearchDataContributor, IVidiunElasticSearchDataContributor
{
	const MAX_CAPTION_FILE_SIZE_FOR_INDEXING = 900000; // limit the size of text which can indexed, the mysql packet size is limited by default to 1M anyway
	const PLUGIN_NAME = 'captionSearch';
	const INDEX_NAME = 'caption_item';
	const SEARCH_FIELD_DATA = 'data';
	const SEARCH_TEXT_SUFFIX = 'csend';
	
	const CAPTION_SEARCH_FLOW_MANAGER_CLASS = 'vCaptionSearchFlowManager';
	
	public static function getPluginName()
	{
		return self::PLUGIN_NAME;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPending::dependsOn()
	 */
	public static function dependsOn()
	{
		$captionDependency = new VidiunDependency(CaptionPlugin::getPluginName());
		
		return array($captionDependency);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunPermissions::isAllowedPartner()
	 */
	public static function isAllowedPartner($partnerId)
	{
		if($partnerId == Partner::BATCH_PARTNER_ID)
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
			'captionAssetItem' => 'CaptionAssetItemService',
		);
		return $map;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEventConsumers::getEventConsumers()
	 */
	public static function getEventConsumers()
	{
		return array(
			self::CAPTION_SEARCH_FLOW_MANAGER_CLASS,
		);
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('CaptionSearchBatchJobType');
			
		if($baseEnumName == 'BatchJobType')
			return array('CaptionSearchBatchJobType');
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		if($baseClass == 'vJobData' && $enumValue == self::getBatchJobTypeCoreValue(CaptionSearchBatchJobType::PARSE_CAPTION_ASSET))
			return new vParseCaptionAssetJobData();
	
		if($baseClass == 'VidiunJobData' && $enumValue == self::getApiValue(CaptionSearchBatchJobType::PARSE_CAPTION_ASSET))
			return new VidiunParseCaptionAssetJobData();
		
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		if($baseClass == 'vJobData' && $enumValue == self::getBatchJobTypeCoreValue(CaptionSearchBatchJobType::PARSE_CAPTION_ASSET))
			return 'vParseCaptionAssetJobData';
	
		if($baseClass == 'VidiunJobData' && $enumValue == self::getApiValue(CaptionSearchBatchJobType::PARSE_CAPTION_ASSET))
			return 'VidiunParseCaptionAssetJobData';
		
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSearchDataContributor::getSearchData()
	 */
	public static function getSearchData(BaseObject $object)
	{
		if($object instanceof entry && self::isAllowedPartner($object->getPartnerId()))
			return self::getCaptionSearchData($object);
			
		return null;
	}
	
	public static function getCaptionSearchData(entry $entry)
	{
		$captionAssets = assetPeer::retrieveByEntryId($entry->getId(), array(CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION)));
		if(!$captionAssets || !count($captionAssets))
			return null;
			
		$data = array();
		foreach($captionAssets as $captionAsset)
		{
			/* @var $captionAsset CaptionAsset */
			
			$syncKey = $captionAsset->getSyncKey(asset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			$content = vFileSyncUtils::file_get_contents($syncKey, true, false, self::MAX_CAPTION_FILE_SIZE_FOR_INDEXING);
			if(!$content)
				continue;
				
	    	$captionsContentManager = vCaptionsContentManager::getCoreContentManager($captionAsset->getContainerFormat());
	    	if(!$captionsContentManager)
	    	{
	    		VidiunLog::err("Captions content manager not found for format [" . $captionAsset->getContainerFormat() . "]");
	    		continue;
	    	}

	    	$content = $captionsContentManager->getContent($content);
	    	if(!$content)
	    		continue;

			$data[] = $captionAsset->getId() . " ca_prefix $content ca_sufix";
		}
		
		$dataField = CaptionSearchPlugin::getSearchFieldName(CaptionSearchPlugin::SEARCH_FIELD_DATA);
		$searchValues = array(
			$dataField => CaptionSearchPlugin::PLUGIN_NAME . ' ' . implode(' ', $data) . ' ' . CaptionSearchPlugin::SEARCH_TEXT_SUFFIX
		);
		
		return $searchValues;
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
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	/**
	 * return field name as appears in index schema
	 * @param string $fieldName
	 */
	public static function getSearchFieldName($fieldName){
		if ($fieldName == self::SEARCH_FIELD_DATA)
			return  'plugins_data';
			
		return CaptionPlugin::getPluginName() . '_' . $fieldName;
	}

	/**
	 * Return textual search data to be associated with the object
	 *
	 * @param BaseObject $object
	 * @return ArrayObject
	 */
	public static function getElasticSearchData(BaseObject $object)
	{
		if($object instanceof entry && self::isAllowedPartner($object->getPartnerId()))
			return self::getCaptionElasticSearchData($object);

		return null;
	}

	public static function getCaptionElasticSearchData($entry)
	{
		$captionAssets = assetPeer::retrieveByEntryId($entry->getId(), array(CaptionPlugin::getAssetTypeCoreValue(CaptionAssetType::CAPTION)));
		if(!$captionAssets || !count($captionAssets))
			return null;

		$data = array();
		$captionData = array();
		foreach($captionAssets as $captionAsset)
		{
			/* @var $captionAsset CaptionAsset */

			$syncKey = $captionAsset->getSyncKey(asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
			$content = vFileSyncUtils::file_get_contents($syncKey, true, false, self::MAX_CAPTION_FILE_SIZE_FOR_INDEXING);
			if(!$content)
				continue;

			$captionsContentManager = vCaptionsContentManager::getCoreContentManager($captionAsset->getContainerFormat());
			if(!$captionsContentManager)
			{
				VidiunLog::err("Captions content manager not found for format [" . $captionAsset->getContainerFormat() . "]");
				continue;
			}

			$items = $captionsContentManager->parse($content);

			if(!$items)
				continue;

			$language = $captionAsset->getLanguage();
			self::getElasticLines($captionData, $items, $language, $captionAsset->getId(), $captionAsset->getLabel());
		}

		$data['caption_assets'] = $captionData;

		return $data;
	}

	protected static function getElasticLines(&$captionData ,$items, $language, $assetId, $label = null)
	{
		foreach ($items as $item)
		{
			$line = array(
				'start_time' => $item['startTime'],
				'end_time' => $item['endTime'],
				'language' => $language,
				'caption_asset_id' => $assetId,
			);

			if($label)
				$line['label'] = $label;

			$content = '';
			foreach ($item['content'] as $curChunk)
				$content .= $curChunk['text'];

			$content = vString::stripUtf8InvalidChars($content);
			$content = vXml::stripXMLInvalidChars($content);
			if(strlen($content) > vElasticSearchManager::MAX_LENGTH)
				$content = substr($content, 0, vElasticSearchManager::MAX_LENGTH);
			$line['content'] = $content;

			$analyzedFieldName = elasticSearchUtils::getAnalyzedFieldName($language, 'content' ,elasticSearchUtils::UNDERSCORE_FIELD_DELIMITER);
			if($analyzedFieldName)
				$line[$analyzedFieldName] = $content;

			$captionData[] = $line;
		}
	}
}
