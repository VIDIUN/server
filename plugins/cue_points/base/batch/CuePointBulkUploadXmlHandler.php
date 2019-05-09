<?php
/**
 * Handles cue point ingestion from XML bulk upload
 * @package plugins.cuePoint
 * @subpackage batch
 */
abstract class CuePointBulkUploadXmlHandler implements IVidiunBulkUploadXmlHandler
{
	/**
	 * @var BulkUploadEngineXml
	 */
	protected $xmlBulkUploadEngine = null;
	
	/**
	 * @var VidiunCuePointClientPlugin
	 */
	protected $cuePointPlugin = null;
	
	/**
	 * @var int
	 */
	protected $entryId = null;
	
	/**
	 * @var array ingested cue points
	 */
	protected $ingested = array();
	
	/**
	 * @var array each item operation
	 */
	protected $operations = array();
	
	/**
	 * @var array of existing Cue Points with systemName
	 */
	protected static $existingCuePointsBySystemName = null;
	
	protected function __construct()
	{
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
		if(!($object instanceof VidiunBaseEntry))
			return;
			
		if(empty($item->scenes))
			return;
			
		$this->entryId = $object->id;
		$this->cuePointPlugin = VidiunCuePointClientPlugin::get(VBatchBase::$vClient);
		
		VBatchBase::impersonate($this->xmlBulkUploadEngine->getCurrentPartnerId());
		VBatchBase::$vClient->startMultiRequest();
	
		$items = array();
		foreach($item->scenes->children() as $scene)
			if($this->addCuePoint($scene))
				$items[] = $scene;
			
		$results = VBatchBase::$vClient->doMultiRequest();
		VBatchBase::unimpersonate();
		
		if(is_array($results) && is_array($items))
			$this->handleResults($results, $items);
	}

	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::handleItemUpdated()
	 */
	public function handleItemUpdated(VidiunObjectBase $object, SimpleXMLElement $item)
	{
		if(!($object instanceof VidiunBaseEntry))
			return;
			
		if(empty($item->scenes))
			return;

		$action = VBulkUploadEngine::$actionsMap[VidiunBulkUploadAction::UPDATE];
		if(isset($item->scenes->action))
			$action = strtolower($item->scenes->action);
			
		switch ($action)
		{
			case VBulkUploadEngine::$actionsMap[VidiunBulkUploadAction::UPDATE]:
				break;
			default:
				throw new VidiunBatchException("scenes->action: $action is not supported", VidiunBatchJobAppErrors::BULK_ACTION_NOT_SUPPORTED);
		}
			
		$this->entryId = $object->id;
		$this->cuePointPlugin = VidiunCuePointClientPlugin::get(VBatchBase::$vClient);
		
		VBatchBase::impersonate($this->xmlBulkUploadEngine->getCurrentPartnerId());
		
		$this->getExistingCuePointsBySystemName($this->entryId);
		VBatchBase::$vClient->startMultiRequest();
		
		$items = array();
		foreach($item->scenes->children() as $scene)
		{
			if($this->updateCuePoint($scene))
				$items[] = $scene;
		}
			
		$results = VBatchBase::$vClient->doMultiRequest();
		VBatchBase::unimpersonate();

		if(is_array($results) && is_array($items))
			$this->handleResults($results, $items);
	}

	/* (non-PHPdoc)
	 * @see IVidiunBulkUploadXmlHandler::handleItemDeleted()
	 */
	public function handleItemDeleted(VidiunObjectBase $object, SimpleXMLElement $item)
	{
		// No handling required
	}

	/**
	 * @param string $entryId
	 * @return array of cuepoint that have systemName
	 */
	protected function getExistingCuePointsBySystemName($entryId)
	{
		if (is_array(self::$existingCuePointsBySystemName))
			return;
		
		$filter = new VidiunCuePointFilter();
		$filter->entryIdEqual = $entryId;
		
		$pager = new VidiunFilterPager();
		$pager->pageSize = 500;
		
		$cuePoints = $this->cuePointPlugin->cuePoint->listAction($filter, $pager);
		self::$existingCuePointsBySystemName = array();
		
		if (!isset($cuePoints->objects))
			return;

		foreach ($cuePoints->objects as $cuePoint)
		{
			if($cuePoint->systemName != '')
				self::$existingCuePointsBySystemName[$cuePoint->systemName] = $cuePoint->id;
		}
	}
	
	
	protected function handleResults(array $results, array $items)
	{
		if(count($results) != count($this->operations) || count($this->operations) != count($items))
		{
			VidiunLog::err("results count [" . count($results) . "] operations count [" . count($this->operations) . "] items count [" . count($items) . "]");
			return;
		}
			
		$pluginsInstances = VidiunPluginManager::getPluginInstances('IVidiunBulkUploadXmlHandler');
		
		foreach($results as $index => $cuePoint)
		{
			if(is_array($cuePoint) && isset($cuePoint['code']))
				throw new Exception($cuePoint['message']);
			
			foreach($pluginsInstances as $pluginsInstance)
			{
				/* @var $pluginsInstance IVidiunBulkUploadXmlHandler */
				
				$pluginsInstance->configureBulkUploadXmlHandler($this->xmlBulkUploadEngine);
				
				if($this->operations[$index] == VidiunBulkUploadAction::ADD)
					$pluginsInstance->handleItemAdded($cuePoint, $items[$index]);
				elseif($this->operations[$index] == VidiunBulkUploadAction::UPDATE)
					$pluginsInstance->handleItemUpdated($cuePoint, $items[$index]);
				elseif($this->operations[$index] == VidiunBulkUploadAction::DELETE)
					$pluginsInstance->handleItemDeleted($cuePoint, $items[$index]);
			}
		}
	}

	/**
	 * @return VidiunCuePoint
	 */
	abstract protected function getNewInstance();

	/**
	 * @param SimpleXMLElement $scene
	 * @return VidiunCuePoint
	 */
	protected function parseCuePoint(SimpleXMLElement $scene)
	{
		$cuePoint = $this->getNewInstance();
		
		if(isset($scene['systemName']) && $scene['systemName'])
			$cuePoint->systemName = $scene['systemName'] . '';
			
		$cuePoint->startTime = kXml::timeToInteger($scene->sceneStartTime);
		
		if(!isset($scene->tags))
			return $cuePoint;
	
		$tags = array();
		foreach ($scene->tags->children() as $tag)
		{
			$value = "$tag";
			if($value)
				$tags[] = $value;
		}
		$cuePoint->tags = implode(',', $tags);
		
		return $cuePoint;
	}
	
	/**
	 * @param SimpleXMLElement $scene
	 */
	protected function addCuePoint(SimpleXMLElement $scene)
	{
		$cuePoint = $this->parseCuePoint($scene);
		if(!$cuePoint)
			return false;
			
		$cuePoint->entryId = $this->entryId;
		$ingestedCuePoint = $this->cuePointPlugin->cuePoint->add($cuePoint);
		$this->operations[] = VidiunBulkUploadAction::ADD;
		if($cuePoint->systemName)
			$this->ingested[$cuePoint->systemName] = $ingestedCuePoint;
			
		return true;
	}

	/**
	 * @param SimpleXMLElement $scene
	 */
	protected function updateCuePoint(SimpleXMLElement $scene)
	{
		$cuePoint = $this->parseCuePoint($scene);
		if(!$cuePoint)
			return false;

		if(isset($scene['sceneId']) && $scene['sceneId'])
		{
			$cuePointId = $scene['sceneId'];
			$ingestedCuePoint = $this->cuePointPlugin->cuePoint->update($cuePointId, $cuePoint);
			$this->operations[] = VidiunBulkUploadAction::UPDATE;
		}
		elseif(isset($cuePoint->systemName) && isset(self::$existingCuePointsBySystemName[$cuePoint->systemName]))
		{
			$cuePoint = $this->removeNonUpdatbleFields($cuePoint);
			$cuePointId = self::$existingCuePointsBySystemName[$cuePoint->systemName];
			$ingestedCuePoint = $this->cuePointPlugin->cuePoint->update($cuePointId, $cuePoint);
			$this->operations[] = VidiunBulkUploadAction::UPDATE;
		}
		else
		{
			$cuePoint->entryId = $this->entryId;
			$ingestedCuePoint = $this->cuePointPlugin->cuePoint->add($cuePoint);
			$this->operations[] = VidiunBulkUploadAction::ADD;
		}
		if($cuePoint->systemName)
			$this->ingested[$cuePoint->systemName] = $ingestedCuePoint;
			
		return true;
	}
	
	/**
	 * @param string $cuePointSystemName
	 * @return string
	 */
	protected function getCuePointId($systemName)
	{
		if(isset($this->ingested[$systemName]))
		{
			$id = $this->ingested[$systemName]->id;
			return "$id";
		}
		return null;
	
//		Won't work in the middle of multi request
//		
//		$filter = new VidiunAnnotationFilter();
//		$filter->entryIdEqual = $this->entryId;
//		$filter->systemNameEqual = $systemName;
//		
//		$pager = new VidiunFilterPager();
//		$pager->pageSize = 1;
//		
//		try
//		{
//			$cuePointListResponce = $this->cuePointPlugin->cuePoint->listAction($filter, $pager);
//		}
//		catch(Exception $e)
//		{
//			return null;
//		}
//		
//		if($cuePointListResponce->totalCount && $cuePointListResponce->objects[0] instanceof VidiunAnnotation)
//			return $cuePointListResponce->objects[0]->id;
//			
//		return null;
	}
	
	/* (non-PHPdoc)
	 * @see IVidiunConfigurator::getContainerName()
	*/
	public function getContainerName()
	{
		return 'scenes';
	}
	
	/**
	 * Removes all non updatble fields from the cuepoint
	 * @param VidiunCuePoint $entry
	 */
	protected function removeNonUpdatbleFields(VidiunCuePoint $cuePoint)
	{
		return $cuePoint;
	}
}
