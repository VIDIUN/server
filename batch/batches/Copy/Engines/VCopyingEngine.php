<?php
/**
 * @package Scheduler
 * @subpackage Copy
 */
abstract class VCopyingEngine
{
	/**
	 * @var VidiunClient
	 */
	protected $client;
	
	/**
	 * @var VidiunFilterPager
	 */
	protected $pager;
	
	/**
	 * @var int
	 */
	private $lastCopyId;
	
	/**
 	 * @var int
 	 */
 	private $lastCreatedAt;
	
	/**
	 * The partner that owns the objects
	 * @var int
	 */
	private $partnerId;
	
	/**
	 * The batch system partner id
	 * @var int
	 */
	private $batchPartnerId;
	
	/**
	 * @param int $objectType of enum VidiunCopyObjectType
	 * @return VCopyingEngine
	 */
	public static function getInstance($objectType)
	{
		switch($objectType)
		{
			case VidiunCopyObjectType::CATEGORY_USER:
				return new VCopyingCategoryUserEngine();
				
			case VidiunCopyObjectType::CATEGORY_ENTRY:
 				return new VCopyingCategoryEntryEngine();
				
			default:
				return VidiunPluginManager::loadObject('VCopyingEngine', $objectType);
		}
	}
	
	/**
	 * @param int $partnerId
	 * @param VidiunClient $client
	 * @param VSchedularTaskConfig $taskConfig
	 */
	public function configure($partnerId)
	{
		$this->partnerId = $partnerId;
		$this->batchPartnerId = VBatchBase::$taskConfig->getPartnerId();

		$this->pager = new VidiunFilterPager();
		$this->pager->pageSize = 100;
		
		if(VBatchBase::$taskConfig->params->pageSize)
			$this->pager->pageSize = VBatchBase::$taskConfig->params->pageSize;
	}
	
	
	/**
	 * @param VidiunFilter $filter The filter should return the list of objects that need to be copied
	 * @param VidiunObjectBase $templateObject Template object to overwrite attributes on the copied object
	 * @return int the number of copied objects
	 */
	public function run(VidiunFilter $filter, VidiunObjectBase $templateObject)
	{
		VBatchBase::impersonate($this->partnerId);
		$ret = $this->copy($filter, $templateObject);
		VBatchBase::unimpersonate();
		
		return $ret;
	}
	
	/**
	 * @param VidiunFilter $filter The filter should return the list of objects that need to be copied
	 * @param VidiunObjectBase $templateObject Template object to overwrite attributes on the copied object
	 * @return int the number of copied objects
	 */
	abstract protected function copy(VidiunFilter $filter, VidiunObjectBase $templateObject);
	
	/**
	 * Creates a new object instance, based on source object and copied attribute from the template object
	 * @param VidiunObjectBase $sourceObject
	 * @param VidiunObjectBase $templateObject
	 * @return VidiunObjectBase
	 */
	abstract protected function getNewObject(VidiunObjectBase $sourceObject, VidiunObjectBase $templateObject);
	
	/**
	 * @return int $lastCopyId
	 */
	public function getLastCopyId()
	{
		return $this->lastCopyId;
	}

	/**
	 * @param int $lastCopyId
	 */
	protected function setLastCopyId($lastCopyId)
	{
		$this->lastCopyId = $lastCopyId;
	}
}
