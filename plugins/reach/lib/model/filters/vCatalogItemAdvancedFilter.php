<?php
/**
 * @package plugins.viewHistory
 * @subpackage model.filters
 */
class vCatalogItemAdvancedFilter extends AdvancedSearchFilterItem
{
	/**
	 * @var int
	 */
	public $serviceTypeEqual;
	
	/**
	 * @var string
	 */
	public $serviceTypeIn;
	
	/**
	 * @var int
	 */
	public $serviceFeatureEqual;
	
	/**
	 * @var string
	 */
	public $serviceFeatureIn;
	
	/**
	 * @var int
	 */
	public $turnAroundTimeEqual;
	
	/**
	 * @var string
	 */
	public $turnAroundTimeIn;
	
	/**
	 * @var string
	 */
	public $sourceLanguageEqual;
	
	/**
	 * @var string
	 */
	public $targetLanguageEqual;
	
	// Class Getters
	public function getServiceTypeEqual() 		{ return $this->serviceTypeEqual; 	}
	public function getServiceTypeIn() 			{ return $this->serviceTypeIn; 		}
	public function getServiceFeatureEqual()	{ return $this->serviceFeatureEqual; }
	public function getServiceFeatureIn()		{ return $this->serviceFeatureIn; }
	public function getTurnAroundTimeEqual()	{ return $this->turnAroundTimeEqual; }
	public function getTurnAroundTimeIn()		{ return $this->turnAroundTimeIn; }
	public function getSourceLanguageEqual()	{ return $this->sourceLanguageEqual; }
	public function getTargetLanguageEqual()	{ return $this->targetLanguageEqual; }
	
	// Class Setters
	public function setServiceTypeEqual($v)		{ $this->serviceTypeEqual = $v; }
	public function setServiceTypeIn($v)		{ $this->serviceTypeIn = $v; }
	public function setServiceFeatureEqual($v)	{ $this->serviceFeatureEqual = $v; }
	public function setServiceFeatureIn($v)		{ $this->serviceFeatureIn = $v; }
	public function setTurnAroundTimeEqual($v)	{ $this->turnAroundTimeEqual = $v; }
	public function setTurnAroundTimeIn($v)		{ $this->turnAroundTimeIn = $v; }
	public function setSourceLanguageEqual($v)	{ $this->sourceLanguageEqual = $v; }
	public function setTargetLanguageEqual($v)	{ $this->targetLanguageEqual = $v; }
	
	
	// Internal functions
	
	/* (non-PHPdoc)
 	 * @see AdvancedSearchFilterItem::applyCondition()
 	 */
	public function applyCondition(IVidiunDbQuery $query)
	{
		$partnerId = vCurrentContext::$partner_id ? vCurrentContext::$partner_id : vCurrentContext::$vs_partner_id;
		
		if($this->getServiceTypeEqual())
		{
			$condition = ReachPlugin::CATALOG_ITEM_INDEX_PREFIX . $partnerId;
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_SERVICE_TYPE . $this->getServiceTypeEqual();
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_SUFFIX . $partnerId;
			
			$query->addMatch('@' . ReachPlugin::getSearchFieldName(ReachPlugin::SEARCH_FIELD_CATALOG_ITEM_DATA) . " " . $condition);
		}
		
		if($this->getServiceTypeIn())
		{
			$serviceTypesStrs = array();
			$serviceTypes = explode(",", $this->getServiceTypeIn());
			foreach($serviceTypes as $serviceType)
			{
				if(!$serviceType)
					continue;
				
				$serviceTypesStrs[] = ReachPlugin::CATALOG_ITEM_INDEX_SERVICE_TYPE . $serviceType;
			}
			
			$condition = ReachPlugin::CATALOG_ITEM_INDEX_PREFIX . $partnerId;
			$condition .= ' (' . implode(' | ', $serviceTypesStrs) . ')';
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_SUFFIX . $partnerId;
			
			$query->addMatch('@' . ReachPlugin::getSearchFieldName(ReachPlugin::SEARCH_FIELD_CATALOG_ITEM_DATA) . " " . $condition);
		}
		
		if($this->getServiceFeatureEqual())
		{
			$condition = ReachPlugin::CATALOG_ITEM_INDEX_PREFIX . $partnerId;
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_SERVICE_FEATURE . $this->getServiceFeatureEqual();
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_SUFFIX . $partnerId;
			
			$query->addMatch('@' . ReachPlugin::getSearchFieldName(ReachPlugin::SEARCH_FIELD_CATALOG_ITEM_DATA) . " " . $condition);
		}
		
		if($this->getServiceFeatureIn())
		{
			$serviceFeatureStrs = array();
			$serviceFeatures = explode(",", $this->getServiceFeatureIn());
			foreach($serviceFeatures as $serviceFeature)
			{
				if(!$serviceFeature)
					continue;
				
				$serviceFeatureStrs[] = ReachPlugin::CATALOG_ITEM_INDEX_SERVICE_FEATURE . $serviceFeature;
			}
			
			$condition = ReachPlugin::CATALOG_ITEM_INDEX_PREFIX . $partnerId;
			$condition .= ' (' . implode(' | ', $serviceFeatureStrs) . ')';
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_SUFFIX . $partnerId;
			
			$query->addMatch('@' . ReachPlugin::getSearchFieldName(ReachPlugin::SEARCH_FIELD_CATALOG_ITEM_DATA) . " " . $condition);
		}
		
		$turnAroundTimeEqual = $this->getTurnAroundTimeEqual();
		if(isset($turnAroundTimeEqual))
		{
			$condition = ReachPlugin::CATALOG_ITEM_INDEX_PREFIX . $partnerId;
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_TURN_AROUND_TIME . $this->getTurnAroundTimeEqual();
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_SUFFIX . $partnerId;
			
			$query->addMatch('@' . ReachPlugin::getSearchFieldName(ReachPlugin::SEARCH_FIELD_CATALOG_ITEM_DATA) . " " . $condition);
		}
		
		$turnAroundTimeIn = $this->getTurnAroundTimeIn();
		if(isset($turnAroundTimeIn))
		{
			$turnAroundStrs = array();
			$turnAroundTimes = explode(",", $turnAroundTimeIn);
			foreach($turnAroundTimes as $turnAroundTime)
			{
				if(!isset($turnAroundTime))
					continue;
				
				$turnAroundStrs[] = ReachPlugin::CATALOG_ITEM_INDEX_TURN_AROUND_TIME . $turnAroundTime;
			}
			
			$condition = ReachPlugin::CATALOG_ITEM_INDEX_PREFIX . $partnerId;
			$condition .= ' (' . implode(' | ', $turnAroundStrs) . ')';
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_SUFFIX . $partnerId;
			
			$query->addMatch('@' . ReachPlugin::getSearchFieldName(ReachPlugin::SEARCH_FIELD_CATALOG_ITEM_DATA) . " " . $condition);
		}
		
		if($this->getSourceLanguageEqual())
		{
			$condition = ReachPlugin::CATALOG_ITEM_INDEX_PREFIX . $partnerId;
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_LANGUAGE . $this->getSourceLanguageEqual();
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_SUFFIX . $partnerId;
			
			$query->addMatch('@' . ReachPlugin::getSearchFieldName(ReachPlugin::SEARCH_FIELD_CATALOG_ITEM_DATA) . " " . $condition);
		}
		
		if($this->getTargetLanguageEqual())
		{
			$condition = ReachPlugin::CATALOG_ITEM_INDEX_PREFIX . $partnerId;
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_TARGET_LANGUAGE . $this->getTargetLanguageEqual();
			$condition .= " " . ReachPlugin::CATALOG_ITEM_INDEX_SUFFIX . $partnerId;
			
			$query->addMatch('@' . ReachPlugin::getSearchFieldName(ReachPlugin::SEARCH_FIELD_CATALOG_ITEM_DATA) . " " . $condition);
		}
	}
}
