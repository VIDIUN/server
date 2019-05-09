<?php
/**
 * @package api
 * @subpackage filters
 */
class vEntryIndexAdvancedFilter extends kIndexAdvancedFilter
{

	/* (non-PHPdoc)
	 * @see AdvancedSearchFilterItem::applyCondition()
	 */
	public function applyCondition(IVidiunDbQuery $query)
	{
		$this->applyConditionImpl($query, "int_id");
	}	
}
