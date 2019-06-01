<?php
/**
 * @package Core
 * @subpackage model.filters.advanced
 */ 
class AdvancedSearchFilterComparableCondition extends AdvancedSearchFilterCondition
{
	/**
	 * @var VidiunSearchConditionComparison
	 */
	public $comparison;

	/**
	 * Adds conditions, matches and where clauses to the query
	 * @param IVidiunIndexQuery $query
	 */
	public function applyCondition(IVidiunDbQuery $query)
	{
		switch ($this->getComparison())
		{
			case VidiunSearchConditionComparison::EQUAL:
				$comparison = ' = ';
				break;
			case VidiunSearchConditionComparison::GREATER_THAN:
				$comparison = ' > ';
				break;
			case VidiunSearchConditionComparison::GREATER_THAN_OR_EQUAL:
				$comparison = ' >= ';
				break;
			case VidiunSearchConditionComparison::LESS_THAN:
				$comparison = " < ";
				break;
			case VidiunSearchConditionComparison::LESS_THAN_OR_EQUAL:
				$comparison = " <= ";
				break;
			case VidiunSearchConditionComparison::NOT_EQUAL:
				$comparison = " <> ";
				break;
			default:
				VidiunLog::err("Missing comparison type");
				return;
		}

		$field = $this->getField();
		$value = $this->getValue();
		$fieldValue = $this->getFieldValue($field);
		if (is_null($fieldValue))
		{
			VidiunLog::err('Unknown field [' . $field . ']');
			return;
		}

		$newCondition = $fieldValue . $comparison . VidiunCriteria::escapeString($value);

		$query->addCondition($newCondition);
	}

	protected function getFieldValue($field)
	{
		$fieldValue = null;
		switch($field)
		{
			case Criteria::CURRENT_DATE:
				$d = getdate();
				$fieldValue = mktime(0, 0, 0, $d['mon'], $d['mday'], $d['year']);
				break;

			case Criteria::CURRENT_TIME:
			case Criteria::CURRENT_TIMESTAMP:
				$fieldValue = time();
				break;
		}
		return $fieldValue ;
	}

	public function addToXml(SimpleXMLElement &$xmlElement)
	{
		parent::addToXml($xmlElement);
		
		$xmlElement->addAttribute('comparison', htmlspecialchars($this->comparison));
	}
	
	public function fillObjectFromXml(SimpleXMLElement $xmlElement)
	{
		parent::fillObjectFromXml($xmlElement);
		
		$attr = $xmlElement->attributes();
		if(isset($attr['comparison']))
			$this->comparison = (string) html_entity_decode($attr['comparison']);
	}
	
	/**
	 * @return VidiunSearchConditionComparison $comparison
	 */
	public function getComparison() {
		return $this->comparison;
	}

	/**
	 * @param VidiunSearchConditionComparison $comparison the $comparison to set
	 */
	public function setComparison($comparison) {
		$this->comparison = $comparison;
	}
}
