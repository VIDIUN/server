<?php
/**
 * @package Core
 * @subpackage model.filters.advanced
 */ 
class AdvancedSearchFilterItem
{
	/**
	 * @var string
	 */
	protected $vidiunClass;
	
	public $filterLimit;
	
	public $overrideFilterLimit;

	final public function apply(baseObjectFilter $filter, IVidiunDbQuery $query)
	{
		if($this->overrideFilterLimit)
		{
			$filter->setLimit($this->overrideFilterLimit);
		}
		$this->filterLimit = $filter->getLimit();
		$this->applyCondition($query);
	}
	
	public function getFreeTextConditions($partnerScope, $freeTexts)
	{
		return array();	
	}
	
	/**
	 * Adds conditions, matches and where clauses to the query
	 * @param IVidiunIndexQuery $query
	 */
	public function applyCondition(IVidiunDbQuery $query)
	{
	}
	
	public function addToXml(SimpleXMLElement &$xmlElement)
	{
		$xmlElement->addAttribute('vidiunClass', $this->vidiunClass);
	}
	
	public function fillObjectFromXml(SimpleXMLElement $xmlElement)
	{
		$attr = $xmlElement->attributes();
		if(isset($attr['vidiunClass']))
			$this->vidiunClass = (string) $attr['vidiunClass'];
	}
	
	/**
	 * @return the $vidiunClass
	 */
	public function getVidiunClass() {
		return $this->vidiunClass;
	}

	/**
	 * @param $vidiunClass the $vidiunClass to set
	 */
	public function setVidiunClass($vidiunClass) {
		$this->vidiunClass = $vidiunClass;
	}
}
