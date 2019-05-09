<?php
/**
 * @package plugins.searchHistory
 * @subpackage lib
 */
class searchHistoryUtils
{

	const DEFAULT_SEARCH_CONTEXT = 'DEFAULT_SC';

	public static function getSearchContext()
	{
		$searchContext = null;
		$vs = vs::fromSecureString(vCurrentContext::$vs);
		if ($vs)
		{
			$searchContext = $vs->getSearchContext();
		}
		return $searchContext ? $searchContext : self::DEFAULT_SEARCH_CONTEXT;
	}

	public static function formatPartnerIdUserIdContext($partnerId, $userId, $context)
	{
		return sprintf("p%su%sc%s", $partnerId, $userId, $context);
	}

}
