<?php
/**
 * @package Core
 * @subpackage model.filters.advanced
 */ 
class AdvancedSearchFilterMatchAttributeCondition extends AdvancedSearchFilterMatchCondition
{
	/**
	 * Adds conditions, matches and where clauses to the query
	 * @param IVidiunDbQuery $query
	 */
	public function applyCondition(IVidiunDbQuery $query)
	{
		if (!$query instanceof IVidiunIndexQuery)
			return;

		$matchText = '"'.VidiunCriteria::escapeString($this->value).'"';
		if ($this->not)
			$matchText = '!'.$matchText;
		$query->addMatch("@$this->field (".$matchText.")");
	}
}
