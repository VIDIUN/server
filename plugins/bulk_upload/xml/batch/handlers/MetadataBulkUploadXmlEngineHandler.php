<?php
/**
 * @package plugins.metadataBulkUploadXml
 * @subpackage lib
 */
class MetadataBulkUploadXmlEngineHandler implements IVidiunBulkUploadXmlHandler
{
	/**
	 * @var VidiunMetadataObjectType
	 */
	private $objectType = VidiunMetadataObjectType::ENTRY;
	
	/**
	 * @var string class name
	 */
	private $objectClass = 'VidiunBaseEntry';
	
	
	/**
	 * @var string XML node name
	 */
	private $containerName = 'customDataItems';
	
	/**
	 * @var string XML node name
	 */
	private $nodeName = 'customData';
	
	/**
	 * @var array<string, int> of metadata profiles by their system name
	 */
	private static $metadataProfiles = null;
	
	/**
	 * @var BulkUploadEngineXml
	 */
	private $xmlBulkUploadEngine = null;
	
	public function __construct($objectType, $objectClass, $nodeName, $containerName = null)
	{
		$this->objectType = $objectType;
		$this->objectClass = $objectClass;
		$this->containerName = $containerName;
		$this->nodeName = $nodeName;
	} 
	
	public function getMetadataProfileId($systemName)
	{
		if(is_null(self::$metadataProfiles))
		{
			$metadataPlugin = VidiunMetadataClientPlugin::get(VBatchBase::$vClient);
			$metadataProfileListResponse = $metadataPlugin->metadataProfile->listAction();
			if(!is_array($metadataProfileListResponse->objects))
				return null;
				
			self::$metadataProfiles = array();
			foreach($metadataProfileListResponse->objects as $metadataProfile)
				if(!is_null($metadataProfile->systemName))
					self::$metadataProfiles[$metadataProfile->systemName] = $metadataProfile->id;
		}
		
		if(isset(self::$metadataProfiles["$systemName"]))
			return self::$metadataProfiles["$systemName"];
			
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::configureBulkUploadXmlHandler()
	 */
	public function configureBulkUploadXmlHandler(BulkUploadEngineXml $xmlBulkUploadEngine)
	{
		$this->xmlBulkUploadEngine = $xmlBulkUploadEngine;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::handleItemAdded()
	 */
	public function handleItemAdded(VidiunObjectBase $object, SimpleXMLElement $item)
	{
		if(!($object instanceof $this->objectClass))
			return;

		$metadataItems = $item;
		if($this->containerName)
		{	
			$containerName = $this->containerName;
			if(empty($item->$containerName))
				return;
				
			$metadataItems = $item->$containerName;
		}
				
		$nodeName = $this->nodeName;
		if(empty($metadataItems->$nodeName)) // if there is no costum data then we exit
			return;
			
		VidiunLog::info("Handles custom metadata for object type [$this->objectType] class [$this->objectClass] id [$object->id] partner id [$object->partnerId]");
			
		VBatchBase::impersonate($this->xmlBulkUploadEngine->getCurrentPartnerId());
		$pluginsErrorResults = array();
		foreach($metadataItems->$nodeName as $customData)
		{			
			try {
				$this->handleCustomData($object->id, $customData);
			}catch (Exception $e)
			{
				VidiunLog::err($this->getContainerName() . ' failed: ' . $e->getMessage());
				$pluginsErrorResults[] = $e->getMessage();
			}
		}
		
		if(count($pluginsErrorResults))
			throw new Exception(implode(', ', $pluginsErrorResults));
			
		VBatchBase::unimpersonate();
	}

	public function handleCustomData($objectId, SimpleXMLElement $customData)
	{
		$action = VBulkUploadEngine::$actionsMap[VidiunBulkUploadAction::REPLACE];
		if(isset($customData->action))
			$action = strtolower($customData->action);
					
		$metadataProfileId = null;
		if(!empty($customData['metadataProfileId']))
			$metadataProfileId = (int)$customData['metadataProfileId'];

		if(!$metadataProfileId && !empty($customData['metadataProfile']))
			$metadataProfileId = $this->getMetadataProfileId($customData['metadataProfile']);
				
		if(!$metadataProfileId)
			throw new VidiunBatchException("Missing custom data metadataProfile attribute", VidiunBatchJobAppErrors::BULK_MISSING_MANDATORY_PARAMETER);
		
		$metadataPlugin = VidiunMetadataClientPlugin::get(VBatchBase::$vClient);
		
		$metadataFilter = new VidiunMetadataFilter();
		$metadataFilter->metadataObjectTypeEqual = $this->objectType;
		$metadataFilter->objectIdEqual = $objectId;
		$metadataFilter->metadataProfileIdEqual = $metadataProfileId;
		
		$pager = new VidiunFilterPager();
		$pager->pageSize = 1;
		
		$metadataListResponse = $metadataPlugin->metadata->listAction($metadataFilter, $pager);
		
		$metadataId = null;
		$metadata = null;
		if(is_array($metadataListResponse->objects) && count($metadataListResponse->objects) > 0)
		{
			$metadata = reset($metadataListResponse->objects);
			$metadataId = $metadata->id;
		}
				
		switch ($action)
		{
			case VBulkUploadEngine::$actionsMap[VidiunBulkUploadAction::TRANSFORM_XSLT]:
				if(!isset($customData->xslt))
					throw new VidiunBatchException($this->containerName . '->' . $this->nodeName . "->xslt element is missing", VidiunBatchJobAppErrors::BULK_ELEMENT_NOT_FOUND);
				
				if($metadata)
					$metadataXml = $metadata->xml;
				else
					$metadataXml = '<metadata></metadata>';
					
				$decodedXslt = vXml::decodeXml($customData->xslt);					
				$metadataXml = vXml::transformXmlUsingXslt($metadataXml, $decodedXslt); 
				break;
			case VBulkUploadEngine::$actionsMap[VidiunBulkUploadAction::REPLACE]:
				if(!isset($customData->xmlData))
					throw new VidiunBatchException($this->containerName . '->' . $this->nodeName . "->xmlData element is missing", VidiunBatchJobAppErrors::BULK_ELEMENT_NOT_FOUND);
				
				$metadataXmlObject = $customData->xmlData->children();
				$metadataXml = $metadataXmlObject->asXML();
				break;
			default:
				throw new VidiunBatchException($this->containerName . '->' . $this->nodeName . "->action: $action is not supported", VidiunBatchJobAppErrors::BULK_ACTION_NOT_SUPPORTED);
		}
		
		if($metadataId)
			$metadataPlugin->metadata->update($metadataId, $metadataXml);
		else
			$metadataPlugin->metadata->add($metadataProfileId, $this->objectType, $objectId, $metadataXml);
	}

	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::handleItemUpdated()
	 */
	public function handleItemUpdated(VidiunObjectBase $object, SimpleXMLElement $item)
	{
		if(!$this->containerName)
			return;
		
		$containerName = $this->containerName;
		if(empty($item->$containerName))
			return;
			
		$metadataItems = $item->$containerName;
		
		$action = VBulkUploadEngine::$actionsMap[VidiunBulkUploadAction::UPDATE];
		if(isset($metadataItems->action))
			$action = strtolower($metadataItems->action);
			
		switch ($action)
		{
			case VBulkUploadEngine::$actionsMap[VidiunBulkUploadAction::UPDATE]:
				$this->handleItemAdded($object, $item);
				break;
			default:
				throw new VidiunBatchException($containerName . "->action: $action is not supported", VidiunBatchJobAppErrors::BULK_ACTION_NOT_SUPPORTED);
		}
	}

	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::handleItemDeleted()
	 */
	public function handleItemDeleted(VidiunObjectBase $object, SimpleXMLElement $item)
	{
		// No handling required
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunConfigurator::getContainerName()
	*/
	public function getContainerName()
	{
		return 'customDataItems';
	}
}
