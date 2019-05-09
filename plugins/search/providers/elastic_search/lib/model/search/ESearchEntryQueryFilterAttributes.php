<?php
/**
 * @package plugins.elasticSearch
 * @subpackage model.search
 */
class ESearchEntryQueryFilterAttributes extends ESearchBaseQueryFilterAttributes
{
	public function getDisplayInSearchFilter()
	{
		$displayInSearchQuery = new vESearchTermQuery(ESearchEntryFieldName::DISPLAY_IN_SEARCH, EntryDisplayInSearchType::SYSTEM);
		$mustNotDisplayInSearchBoolQuery = new vESearchBoolQuery();
		$mustNotDisplayInSearchBoolQuery->addToMustNot($displayInSearchQuery);

		$ignoreDisplayInSearchQueries = array();

		foreach	($this->ignoreDisplayInSearchValues as $key => $value)
		{
			if($value)
				$ignoreDisplayInSearchQueries[] = new vESearchTermsQuery($key, $value);
		}

		if(count($ignoreDisplayInSearchQueries))
		{
			$displayInSearchBoolQuery = new vESearchBoolQuery();
			$displayInSearchBoolQuery->addQueriesToShould($ignoreDisplayInSearchQueries);
			$displayInSearchBoolQuery->addToShould($mustNotDisplayInSearchBoolQuery);
			return $displayInSearchBoolQuery;
		}

		return $mustNotDisplayInSearchBoolQuery;
	}
}