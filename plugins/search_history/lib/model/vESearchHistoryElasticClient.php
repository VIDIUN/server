<?php
/**
 * @package plugins.searchHistory
 * @subpackage model
 */
class vESearchHistoryElasticClient
{

	const INDEX_KEY = 'index';
	const TYPE_KEY = 'type';
	const BODY_KEY = 'body';
	const QUERY_KEY = 'query';
	const ACTION_KEY = '_action';
	const DELETE_KEY = 'delete';
	const IDS_TO_DELETE = 'ids_to_delete';
	const MAX_SEARCH_TERMS_TO_DELETE = 1000;

	protected $client;

	public function __construct()
	{
		$this->client = new elasticClient();
	}

	public function deleteSearchTermForUser($searchTerm)
	{
		$partnerId = vCurrentContext::getCurrentPartnerId();
		$vuserId = vCurrentContext::getCurrentVsVuserId();
		if (!$vuserId)
		{
			throw new vESearchHistoryException('Invalid userId', vESearchHistoryException::INVALID_USER_ID);
		}

		$deleteByQuery = new vESearchBoolQuery();
		$partnerTerm = new vESearchTermQuery(ESearchHistoryFieldName::PARTNER_ID, $partnerId);
		$deleteByQuery->addToFilter($partnerTerm);
		$vuserTerm = new vESearchTermQuery(ESearchHistoryFieldName::VUSER_ID, $vuserId);
		$deleteByQuery->addToFilter($vuserTerm);
		$searchContextTerm = new vESearchTermQuery(ESearchHistoryFieldName::SEARCH_CONTEXT, searchHistoryUtils::getSearchContext());
		$deleteByQuery->addToFilter($searchContextTerm);
		$searchTermQuery = new vESearchTermQuery(ESearchHistoryFieldName::SEARCH_TERM, elasticSearchUtils::formatSearchTerm($searchTerm));
		$deleteByQuery->addToFilter($searchTermQuery);
		$query = array(
			self::INDEX_KEY => ESearchHistoryIndexMap::SEARCH_HISTORY_SEARCH_ALIAS,
			self::TYPE_KEY => ESearchHistoryIndexMap::SEARCH_HISTORY_TYPE,
			self::BODY_KEY => array(
				vESearchQueryManager::FROM_KEY => 0,
				vESearchQueryManager::SIZE_KEY => self::MAX_SEARCH_TERMS_TO_DELETE,
				self::QUERY_KEY => $deleteByQuery->getFinalQuery()
			)
		);

		$result = $this->client->search($query, true);
		$ids = vESearchHistoryCoreAdapter::getIdsToDeleteFromHitsResults($result);
		if (!$ids)
			return;

		$body = array(
			self::ACTION_KEY => self::DELETE_KEY,
			'_'.self::TYPE_KEY => ESearchHistoryIndexMap::SEARCH_HISTORY_TYPE,
			self::IDS_TO_DELETE => $ids
		);
		$document = json_encode($body);
		try
		{
			$constructorArgs['exchangeName'] = vESearchHistoryManager::HISTORY_EXCHANGE_NAME;
			$queueProvider = QueueProvider::getInstance(null, $constructorArgs);
			$queueProvider->send(vESearchHistoryManager::HISTORY_QUEUE_NAME, $document);
		}
		catch (Exception $e)
		{
			//don't fail the request, just log
			VidiunLog::err("cannot connect to rabbit");
		}
	}

	public function searchRecentForUser($queryBody)
	{
		$query = array(
			self::INDEX_KEY => ESearchHistoryIndexMap::SEARCH_HISTORY_SEARCH_ALIAS,
			self::TYPE_KEY => ESearchHistoryIndexMap::SEARCH_HISTORY_TYPE,
			self::BODY_KEY => $queryBody
		);

		$result = $this->client->search($query, true);
		return $result;
	}

}
