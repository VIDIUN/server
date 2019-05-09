<?php
/**
 * @package plugins.elasticSearch
 */
class ElasticSearchPlugin extends VidiunPlugin implements IVidiunEventConsumers, IVidiunPending, IVidiunServices, IVidiunObjectLoader, IVidiunExceptionHandler, IVidiunEnumerator
{
    const PLUGIN_NAME = 'elasticSearch';
    const ELASTIC_SEARCH_MANAGER = 'vElasticSearchManager';
    const ELASTIC_CORE_EXCEPTION = 'vESearchException';

    public static function getPluginName()
    {
        return self::PLUGIN_NAME;
    }

    /**
     * @return array
     */
    public static function getEventConsumers()
    {
        return array(
            self::ELASTIC_SEARCH_MANAGER,
        );
    }

    /**
     * Returns a Vidiun dependency object that defines the relationship between two plugins.
     *
     * @return array<VidiunDependency> The Vidiun dependency object
     */
    public static function dependsOn()
    {
        $searchDependency = new VidiunDependency(SearchPlugin::getPluginName());
        return array($searchDependency);
    }

    public static function getServicesMap()
    {
        $map = array(
            'ESearch' => 'ESearchService',
        );
        return $map;
    }

    /* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
    public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
    {
        if ($baseClass == 'VidiunESearchItemData' && $enumValue == VidiunESearchItemDataType::CAPTION)
            return new VidiunESearchCaptionItemData();

        if ($baseClass == 'ESearchItemData' && $enumValue == ESearchItemDataType::CAPTION)
            return new ESearchCaptionItemData();

        if ($baseClass == 'VidiunESearchItemData' && $enumValue == VidiunESearchItemDataType::METADATA)
            return new VidiunESearchMetadataItemData();

        if ($baseClass == 'ESearchItemData' && $enumValue == ESearchItemDataType::METADATA)
            return new ESearchMetadataItemData();

        if ($baseClass == 'VidiunESearchItemData' && $enumValue == VidiunESearchItemDataType::CUE_POINTS)
            return new VidiunESearchCuePointItemData();

        if ($baseClass == 'ESearchItemData' && $enumValue == ESearchItemDataType::CUE_POINTS)
            return new ESearchCuePointItemData();
        
        if ($baseClass == 'VObjectExportEngine' && $enumValue == VidiunExportObjectType::ESEARCH_MEDIA)
        {
        	return new VExportMediaEsearchEngine($constructorArgs);
        }
	
	    if($baseClass == 'VidiunJobData' && $enumValue == BatchJobType::EXPORT_CSV && (isset($constructorArgs['coreJobSubType']) &&  $constructorArgs['coreJobSubType']== self::getExportTypeCoreValue(EsearchMediaEntryExportObjectType::ESEARCH_MEDIA)))
	    {
		    return new VidiunMediaEsearchExportToCsvJobData();
	    }
	
	    if ($baseClass == 'VidiunESearchOrderByItem' && $enumValue == 'ESearchMetadataOrderByItem')
	    {
		    return new VidiunESearchMetadataOrderByItem($constructorArgs);
	    }
	
        return null;
    }

    /* (non-PHPdoc)
	* @see IVidiunObjectLoader::loadObject()
	*/
    public static function getObjectClass($baseClass, $enumValue)
    {
       return null;
    }

    public static function handleESearchException($exception)
    {
        $code = $exception->getCode();
        $data = $exception->getData();
        switch ($code)
        {
            case vESearchException::SEARCH_TYPE_NOT_ALLOWED_ON_FIELD:
                $object = new VidiunAPIException(VidiunESearchErrors::SEARCH_TYPE_NOT_ALLOWED_ON_FIELD, $data['itemType'], $data['fieldName']);
                break;
            case vESearchException::EMPTY_SEARCH_TERM_NOT_ALLOWED:
                $object = new VidiunAPIException(VidiunESearchErrors::EMPTY_SEARCH_TERM_NOT_ALLOWED, $data['fieldName'], $data['itemType']);
                break;
            case vESearchException::SEARCH_TYPE_NOT_ALLOWED_ON_UNIFIED_SEARCH:
                $object = new VidiunAPIException(VidiunESearchErrors::SEARCH_TYPE_NOT_ALLOWED_ON_UNIFIED_SEARCH, $data['itemType']);
                break;
            case vESearchException::EMPTY_SEARCH_ITEMS_NOT_ALLOWED:
                $object = new VidiunAPIException(VidiunESearchErrors::EMPTY_SEARCH_ITEMS_NOT_ALLOWED);
                break;
            case vESearchException::UNMATCHING_BRACKETS:
                $object = new VidiunAPIException(VidiunESearchErrors::UNMATCHING_BRACKETS);
                break;
            case vESearchException::MISSING_QUERY_OPERAND:
                $object = new VidiunAPIException(VidiunESearchErrors::MISSING_QUERY_OPERAND);
                break;
            case vESearchException::UNMATCHING_QUERY_OPERAND:
                $object = new VidiunAPIException(VidiunESearchErrors::UNMATCHING_QUERY_OPERAND);
                break;
            case vESearchException::CONSECUTIVE_OPERANDS_MISMATCH:
                $object = new VidiunAPIException(VidiunESearchErrors::CONSECUTIVE_OPERANDS_MISMATCH);
                break;
            case vESearchException::INVALID_FIELD_NAME:
                $object = new VidiunAPIException(VidiunESearchErrors::INVALID_FIELD_NAME, $data['fieldName']);
                break;
            case vESearchException::MISSING_MANDATORY_PARAMETERS_IN_ORDER_ITEM:
                $object = new VidiunAPIException(VidiunESearchErrors::MISSING_MANDATORY_PARAMETERS_IN_ORDER_ITEM);
                break;
            case vESearchException::MIXED_SEARCH_ITEMS_IN_NESTED_OPERATOR_NOT_ALLOWED:
                $object = new VidiunAPIException(VidiunESearchErrors::MIXED_SEARCH_ITEMS_IN_NESTED_OPERATOR_NOT_ALLOWED);
                break;
            case vESearchException::MISSING_OPERATOR_TYPE:
                $object = new VidiunAPIException(VidiunESearchErrors::MISSING_OPERATOR_TYPE);
                break;

            default:
                $object = null;
        }
        return $object;
    }

    public function getExceptionMap()
    {
        return array(
            self::ELASTIC_CORE_EXCEPTION => array('ElasticSearchPlugin', 'handleESearchException'),
        );
    }
	
	/**
	 * @return int id of dynamic enum in the DB.
	 */
	public static function getExportTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('ExportObjectType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('EsearchMediaEntryExportObjectType');
		
		if($baseEnumName == 'ExportObjectType')
			return array('EsearchMediaEntryExportObjectType');
		
		return array();
	}
}
