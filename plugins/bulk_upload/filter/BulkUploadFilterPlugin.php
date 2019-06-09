<?php
/**
 * @package plugins.bulkUploadFilter
 */
class BulkUploadFilterPlugin extends VidiunPlugin implements IVidiunBulkUpload, IVidiunPending
{
	const PLUGIN_NAME = 'bulkUploadFilter';
	
	/**
	 *
	 * Returns the plugin name
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
		$bulkUploadDependency = new VidiunDependency(BulkUploadPlugin::PLUGIN_NAME);
		
		$bulkUploadXmlDependency = new VidiunDependency(BulkUploadXmlPlugin::PLUGIN_NAME);
		
		return array($bulkUploadDependency, $bulkUploadXmlDependency);
	}
	
	/**
	 * @return array<string> list of enum classes names that extend the base enum name
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('BulkUploadFilterType', 'BulkUploadJobObjectType');
	
		if($baseEnumName == 'BulkUploadType')
			return array('BulkUploadFilterType');
		
		if ($baseEnumName == 'BulkUploadObjectType')
			return array('BulkUploadJobObjectType');
		
		return array();
	}
	
	/**
	 * @param string $baseClass
	 * @param string $enumValue
	 * @param array $constructorArgs
	 * @return object
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		 //Gets the right job for the engine
		if($baseClass == 'vBulkUploadJobData' && (!$enumValue || $enumValue == self::getBulkUploadTypeCoreValue(BulkUploadFilterType::FILTER)))
			return new vBulkUploadFilterJobData();
		
		 //Gets the right job for the engine
		if($baseClass == 'VidiunBulkUploadJobData' && (!$enumValue || $enumValue == self::getBulkUploadTypeCoreValue(BulkUploadFilterType::FILTER)))
			return new VidiunBulkUploadFilterJobData();
			
		 //Gets the service data for the engine
//		if($baseClass == 'VidiunBulkServiceData' && (!$enumValue || $enumValue == self::getBulkUploadTypeCoreValue(BulkUploadFilterType::FILTER)))
//			return new VidiunBulkServiceFilterData();
			
		
		//Gets the engine (only for clients)
		if($baseClass == 'VBulkUploadEngine' && class_exists('VidiunClient') && (!$enumValue || $enumValue == VidiunBulkUploadType::FILTER))
		{
			list($job) = $constructorArgs;
			/* @var $job VidiunBatchJob */
			switch ($job->data->bulkUploadObjectType)
			{
			    case VidiunBulkUploadObjectType::CATEGORY_ENTRY:
			        return new BulkUploadCategoryEntryEngineFilter($job);
			    case VidiunBulkUploadObjectType::USER_ENTRY:
				return new BulkUploadUserEntryEngineFilter($job);
			    case VidiunBulkUploadObjectType::ENTRY:
				return new BulkUploadMediaEntryEngineFilter($job);
			    default:
			        throw new VidiunException("Bulk upload object type [{$job->data->bulkUploadObjectType}] not found", VidiunBatchJobAppErrors::ENGINE_NOT_FOUND);
			        break;
			}
			
		}
				
		return null;
	}

	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::getObjectClass()
	 */
	public static function getObjectClass($baseClass, $enumValue)
	{
		// BulkUploadResultPeer::OM_CLASS = 'BulkUploadResult'
		if ($baseClass == 'BulkUploadResult' && $enumValue == self::getBulkUploadObjectTypeCoreValue(BulkUploadJobObjectType::JOB))
		{
			return 'BulkUploadResultJob';
		}
		
		return null;
	}
	
	/**
	 * Returns the correct file extension for bulk upload type
	 * @param int $enumValue code API value
	 */
	public static function getFileExtension($enumValue)
	{
		if($enumValue == self::getBulkUploadTypeCoreValue(BulkUploadFilterType::FILTER))
			return null;
	}
	
	
	/**
	 * Returns the log file for bulk upload job
	 * @param BatchJob $batchJob bulk upload batchjob
	 */
	public static function writeBulkUploadLogFile($batchJob)
	{
		if($batchJob->getJobSubType() && ($batchJob->getJobSubType() != self::getBulkUploadTypeCoreValue(BulkUploadFilterType::FILTER))){
			return;
		}
		//TODO:
		header("Content-Type: text/plain; charset=UTF-8");
				$criteria = new Criteria();
		$criteria->add(BulkUploadResultPeer::BULK_UPLOAD_JOB_ID, $batchJob->getId());
		$criteria->addAscendingOrderByColumn(BulkUploadResultPeer::LINE_INDEX);
		$criteria->setLimit(100);
		$bulkUploadResults = BulkUploadResultPeer::doSelect($criteria);
		
		if(!count($bulkUploadResults))
			die("Log file is not ready");
			
		$STDOUT = fopen('php://output', 'w');
		$data = $batchJob->getData();
        /* @var $data vBulkUploadFilterJobData */		
		$handledResults = 0;
		while(count($bulkUploadResults))
		{
			$handledResults += count($bulkUploadResults);
			foreach($bulkUploadResults as $bulkUploadResult)
			{				
	            $values = array();
	            $values['bulkUploadResultStatus'] = $bulkUploadResult->getStatus();
				$values['objectId'] = $bulkUploadResult->getObjectId();
				$values['objectStatus'] = $bulkUploadResult->getObjectStatus();
				$values['errorDescription'] = preg_replace('/[\n\r\t]/', ' ', $bulkUploadResult->getErrorDescription());
					
				fwrite($STDOUT, print_r($values,true));
			}
			
    		if(count($bulkUploadResults) < $criteria->getLimit())
    			break;
	    		
    		vMemoryManager::clearMemory();
    		$criteria->setOffset($handledResults);
			$bulkUploadResults = BulkUploadResultPeer::doSelect($criteria);
		}
		fclose($STDOUT);
		
		vFile::closeDbConnections();
		exit;
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
	public static function getBulkUploadObjectTypeCoreValue($valueName)
	{
		$value = self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
		return vPluginableEnumsManager::apiToCore('BulkUploadObjectType', $value);
	}
	
	/**
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}

}
