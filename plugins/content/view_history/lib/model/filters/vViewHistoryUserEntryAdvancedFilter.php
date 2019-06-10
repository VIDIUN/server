<?php
/**
 * @package plugins.viewHistory
 * @subpackage model.filters
 */
class vViewHistoryUserEntryAdvancedFilter extends AdvancedSearchFilterItem
{
	/**
	 * @var UserEntryFilter
	 */
	public $filter;
	
	protected $disable;
	const ENTRIES_COUNT = 200;
	const USER_ENTRIES_LIMIT = 2000;
	const MAX_USER_ENTRY_CHUNK_SIZE  = 100;
	const MIN_USER_ENTRY_CHUNK_SIZE  = 20;
	
	/**
	 * Function to retrieve
	 */
	public function getObjectCount()
	{
		$c = new Criteria();
		$filter->attachToCriteria($criteria);
		
		return UserEntryPeer::doCount($c);
	}
	
	public function addToXml(SimpleXMLElement &$xmlElement)
	{
		parent::addToXml($xmlElement);
			
		if(!is_null($this->filter))
		{
			foreach($this->filter->getFields() as $name => $value)
			{
				if(!is_null($value))
					$xmlElement->addAttribute($name, $value);
			}
		}
	}
	
	public function fillObjectFromXml(SimpleXMLElement $xmlElement)
	{
		parent::fillObjectFromXml($xmlElement);
	
		$attr = $xmlElement->attributes();
		if(is_null($this->filter))
			$this->filter = new UserEntryFilter();
		foreach($attr as $name => $value)
		{
			if(!is_null($value))
			{
				$this->filter->set($name,(string)$value);
			}
		}
	}
	
	public function applyCondition(IVidiunDbQuery $query)
	{
		/* @var $query VidiunCriteria */
		if ($this->disable)
		{
			return;
		}
		$entryIds = array();
		$limit = $query->getLimit() ? $query->getLimit() : ($this->filterLimit ? $this->filterLimit : self::ENTRIES_COUNT);
		
		$query->setLimit(self::ENTRIES_COUNT);
		if ($this->filterLimit)
		{
			VidiunLog::info ("Overriding filter limit to " . self::ENTRIES_COUNT);
			$this->overrideFilterLimit = self::ENTRIES_COUNT;
		}
		
		$this->disable = true;
		
		$entries = entryPeer::doSelect($query);
		$totalCountEntries = $query->getRecordsCount();
		$query->setLimit($limit);
		if ($this->filterLimit)
		{
			$this->overrideFilterLimit = $limit;
		}
		
		if ($totalCountEntries <= self::ENTRIES_COUNT)
		{
			VidiunLog::info("Few entries found - merge with query");
			$ids = array();
			foreach ($entries as $entry)
			{
				$ids[] = $entry->getId();
			}
			
			$entryIds = UserEntryPeer::getEntryIdsByFilter($limit, 0, $this->filter, $ids);
		}	
	
		else 
		{
			VidiunLog::info("Too many entries found - query userEntries instead");
			$userEntryOffset = 0;
			$chunkSize = max(min($limit * 2, self::MAX_USER_ENTRY_CHUNK_SIZE), self::MIN_USER_ENTRY_CHUNK_SIZE);
			if ($this->filterLimit)
			{
				$chunkSize = $limit;
			}
			
			while (true)
			{
				if (count($entryIds) >= $limit)
				{
					VidiunLog::info("Enough entry IDs retrieved");
					break;
				}
				if ($userEntryOffset > self::USER_ENTRIES_LIMIT)
				{
					VidiunLog::info("Not all objects will return from the search - consider narrowing the search criteria");
					break;
				}
				
				$currEntryIds = UserEntryPeer::getEntryIdsByFilter($chunkSize, $userEntryOffset, $this->filter);
				if(!count($currEntryIds))
	            {
	                    break;
	            }
				
				$query->addColumnWhere(entryPeer::ID, $currEntryIds, VidiunCriteria::IN);
				$query->forcedOrderIds = $currEntryIds;
				$query->setLimit($chunkSize);
				$entries = entryPeer::doSelect($query);
				foreach($entries as $entry)
				{
					if(count($entryIds) >= $limit)
					{
						break;
					}
					$entryIds[] = $entry->getId();
				}
				
				if (count($currEntryIds) < $chunkSize)
				{
					break;
				}
				
				$userEntryOffset += $chunkSize;
				vMemoryManager::clearMemory();
			}
			
			$query->setLimit($limit);
			
		}
		
		if (!count($entryIds))
		{
			VidiunLog::err("No user entries found - returning empty result");
			$entryIds = array (entry::ENTRY_ID_THAT_DOES_NOT_EXIST);
		}
		
		if($query instanceof SphinxCriteria)
		{
			$query->forcedOrderIds = $entryIds;
		}
		
		$this->disable = false;
		$query->addColumnWhere(entryPeer::ID, $entryIds, VidiunCriteria::IN);
		
	}
}
