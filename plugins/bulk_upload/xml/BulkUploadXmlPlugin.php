<?php
/**
 * @package plugins.bulkUploadXml
 */
class BulkUploadXmlPlugin extends VidiunPlugin implements IVidiunBulkUpload, IVidiunVersion, IVidiunSchemaDefiner, IVidiunPending, IVidiunEventConsumers
{
	const PLUGIN_NAME = 'bulkUploadXml';
	const PLUGIN_VERSION_MAJOR = 1;
	const PLUGIN_VERSION_MINOR = 1;
	const PLUGIN_VERSION_BUILD = 0;
	
	const BULKUPLOAD_XML_FLOW_MANAGER = "vBulkUploadXmlFlowManager";
	
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
		$drmDependency = new VidiunDependency(BulkUploadPlugin::PLUGIN_NAME);
		
		return array($drmDependency);
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
	 * @see IVidiunEnumerator::getEnums()
	 */
	public static function getEnums($baseEnumName = null)
	{
		if(is_null($baseEnumName))
			return array('BulkUploadXmlType', 'XmlSchemaType');
		
		if($baseEnumName == 'BulkUploadType')
			return array('BulkUploadXmlType');
			
		if($baseEnumName == 'SchemaType')
			return array('XmlSchemaType');
			
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunObjectLoader::loadObject()
	 */
	public static function loadObject($baseClass, $enumValue, array $constructorArgs = null)
	{
		//Gets the right job for the engine
		if($baseClass == 'vBulkUploadJobData' && $enumValue == self::getBulkUploadTypeCoreValue(BulkUploadXmlType::XML))
			return new vBulkUploadXmlJobData();
		
		 //Gets the right job for the engine
		if($baseClass == 'VidiunBulkUploadJobData' && $enumValue == self::getBulkUploadTypeCoreValue(BulkUploadXmlType::XML))
			return new VidiunBulkUploadXmlJobData();
		
		//Gets the engine (only for clients)
		if($baseClass == 'VBulkUploadEngine' && class_exists('VidiunClient') && $enumValue == VidiunBulkUploadType::XML)
		{
			list($job) = $constructorArgs;
			return new BulkUploadEngineXml($job);
		}
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
		if($batchJob->getJobSubType() != self::getBulkUploadTypeCoreValue(BulkUploadXmlType::XML)){
			return;
		}
		
		$xmlElement = self::getBulkUploadMrssXml($batchJob);
		if(is_null($xmlElement)){
			
			echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><mrss><error>Log file is not ready: ".$batchJob->getMessage()."</error></mrss>";
			vFile::closeDbConnections();
			exit;
		}
		echo $xmlElement->asXML();
		vFile::closeDbConnections();
		exit;
		
	}
	
	/**
	 * Returns the log file for bulk upload job
	 * @param BatchJob $batchJob bulk upload batchjob
	 * @return SimpleXMLElement
	 */
	public static function getBulkUploadMrssXml($batchJob){
		
		$actionsMap = array(
			BulkUploadAction::ADD => 'add',
			BulkUploadAction::UPDATE => 'update',
			BulkUploadAction::DELETE => 'delete',
		);
		
		$criteria = new Criteria();
		$criteria->add(BulkUploadResultPeer::BULK_UPLOAD_JOB_ID, $batchJob->getId());
		$criteria->addAscendingOrderByColumn(BulkUploadResultPeer::LINE_INDEX);
		$criteria->setLimit(100);
		$bulkUploadResults = BulkUploadResultPeer::doSelect($criteria);
		
		if(!count($bulkUploadResults)){
			return null;
		}

		header("Content-Type: text/xml; charset=UTF-8");
		
		$data = $batchJob->getData();
		
		$xmlElement = new SimpleXMLElement('<mrss xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>');
		$xmlElement->addAttribute('version', '2.0');
		
		$channel = $xmlElement->addChild('channel');
		
		$handledResults = 0;
		while(count($bulkUploadResults))
		{
			$handledResults += count($bulkUploadResults);
			
	//		insert all entries to instance pool
			$pks = array();
			foreach($bulkUploadResults as $bulkUploadResult){
				/* @var $bulkUploadResult BulkUploadResult */
				$pks[] = $bulkUploadResult->getEntryId();
			}
			entryPeer::retrieveByPKs($pks);
			
			foreach($bulkUploadResults as $bulkUploadResult){
				/* @var $bulkUploadResult BulkUploadResult */
				$item = $channel->addChild('item');
				
				
				$result = $item->addChild('result');
				$result->addChild('errorDescription', self::stringToSafeXml($bulkUploadResult->getErrorDescription()));
	//			$result->addChild('entryStatus', self::stringToSafeXml($bulkUploadResult->getEntryStatus()));
	//			$result->addChild('entryStatusName', self::stringToSafeXml($title));
	
				$action = (isset($actionsMap[$bulkUploadResult->getAction()]) ? $actionsMap[$bulkUploadResult->getAction()] : $actionsMap[BulkUploadAction::ADD]);
				$item->addChild('action', $action);
				
				$entry = $bulkUploadResult->getObject();
				if(!$entry)
					continue;
					
				vMrssManager::getEntryMrssXml($entry, $item);
			}
	    		
    		if(count($bulkUploadResults) < $criteria->getLimit())
    			break;
    			
    		vMemoryManager::clearMemory();
    		$criteria->setOffset($handledResults);
			$bulkUploadResults = BulkUploadResultPeer::doSelect($criteria);
		}
		
		return $xmlElement;
	}
	
    /**
	 * @param string $string
	 * @return string
	 */
	private static function stringToSafeXml($string)
	{
		$string = @iconv('utf-8', 'utf-8', $string);
		$safe = vString::xmlEncode($string);
		return $safe;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunBulkUpload::getFileExtension()
	 */
	public static function getFileExtension($enumValue)
	{
		if($enumValue == self::getBulkUploadTypeCoreValue(BulkUploadXmlType::XML))
			return 'xml';
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunSchemaDefiner::getPluginSchema()
	 */
	public static function getPluginSchema($type)
	{
		$coreType = vPluginableEnumsManager::apiToCore('SchemaType', $type);
		if($coreType == self::getSchemaTypeCoreValue(XmlSchemaType::BULK_UPLOAD_XML))
			return new SimpleXMLElement(file_get_contents(dirname(__FILE__) . '/xml/ingestion.xsd'));
		if($coreType == self::getSchemaTypeCoreValue(XmlSchemaType::BULK_UPLOAD_RESULT_XML))
			return new SimpleXMLElement(file_get_contents(dirname(__FILE__) . '/xml/bulkUploadResult.xsd.xml'));
			
		return null;
			
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
	 * @return string external API value of dynamic enum.
	 */
	public static function getApiValue($valueName)
	{
		return self::getPluginName() . IVidiunEnumerator::PLUGIN_VALUE_DELIMITER . $valueName;
	}
	
	public static function getEventConsumers()
	{
		return array(self::BULKUPLOAD_XML_FLOW_MANAGER);
	}
}
