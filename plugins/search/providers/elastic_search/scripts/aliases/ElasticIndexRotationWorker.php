<?php
/**
 * @package plugins.elasticSearch
 * @subpackage scripts
 */
class ElasticIndexRotationWorker
{

	const ACTIONS = 'actions';
	const ADD = 'add';
	const REMOVE = 'remove';
	const INDEX = 'index';
	const ALIAS = 'alias';

	protected $configSection;
	protected $dryRun;

	//section config
	protected $client;
	protected $indexPattern;
	protected $indexAlias;
	protected $searchAlias;
	protected $maxNumberOfIndices;
	protected $mappingPath;
	protected $indexDateFormat;

	public function __construct($configSection, $dryRun)
	{
		$this->configSection = $configSection;
		$this->dryRun = $dryRun;
	}

	public function rotate()
	{
		$this->validateConfig();
		$this->loadConfig();
		$this->runRotate();
	}

	protected function validateConfig()
	{
		if (!isset($this->configSection['elasticServer']) ||
			!isset($this->configSection['indexPattern']) ||
			!isset($this->configSection['indexAlias']) ||
			!isset($this->configSection['searchAlias']) ||
			!isset($this->configSection['maxNumberOfIndices']) ||
			!isset($this->configSection['newIndexMappingPath']) ||
			!isset($this->configSection['indexDateFormat']))
		{
			die("Missing configuration params. please verify ini file\n");
		}
	}

	protected function loadConfig()
	{
		$elasticServer = $this->configSection['elasticServer'];
		$elasticPort = isset($this->configSection['elasticPort']) ? $this->configSection['elasticPort'] : 9200;
		$this->client = $client = new elasticClient($elasticServer, $elasticPort);
		$this->indexPattern = $this->configSection['indexPattern'];
		$this->indexAlias =  $this->configSection['indexAlias'];
		$this->searchAlias = $this->configSection['searchAlias'];
		$this->maxNumberOfIndices = $this->configSection['maxNumberOfIndices'];
		$this->mappingPath = $this->configSection['newIndexMappingPath'];
		$this->indexDateFormat = $this->configSection['indexDateFormat'];
	}

	protected function changeAliases($aliasesToAdd, $aliasesToRemove)
	{
		$body = array();
		foreach ($aliasesToRemove as $removeAlias)
		{
			/**
			 * @var ElasticIndexAlias $removeAlias
			 */
			$body[self::ACTIONS][] = array(
				self::REMOVE => array(
					self::INDEX => $removeAlias->getIndexName(),
					self::ALIAS => $removeAlias->getAliasName()
				)
			);
		}

		foreach ($aliasesToAdd as $addAlias)
		{
			/**
			 * @var ElasticIndexAlias $addAlias
			 */
			$body[self::ACTIONS][] = array(
				self::ADD => array(
					self::INDEX => $addAlias->getIndexName(),
					self::ALIAS => $addAlias->getAliasName()
				)
			);
		}

		VidiunLog::debug('Change Aliases request body: '.print_r($body, true));

		if (!$this->dryRun)
		{
			$response = $this->client->changeAliases($body);
			if (isset($response['acknowledged']))
			{
				VidiunLog::log('Changed Aliases');
			}
		}
		else
		{
			VidiunLog::debug('Dry Run - Didn\'t changed aliases');
		}
	}

	protected function getCurrentStateMap()
	{
		$currentSearchingIndices = array();
		$currentIndexingIndices = array();

		//get the current state
		$response = $this->client->getAliasesForIndicesByIndexName($this->indexPattern . '*');

		if (!$response)
		{
			die("Could not get alias info\n");
		}

		foreach ($response as $indexName => $arr)
		{
			$aliases = isset($arr['aliases']) ? $arr['aliases'] : array();
			foreach ($aliases as $aliasName => $value)
			{
				if ($aliasName == $this->indexAlias)
				{
					$currentIndexingIndices[] = $indexName;
				}

				if ($aliasName == $this->searchAlias)
				{
					$currentSearchingIndices[] = $indexName;
				}
			}
		}

		$currentSearchingIndices = $this->sortSearchIndices($currentSearchingIndices);

		return array($currentIndexingIndices, $currentSearchingIndices);
	}

	protected function sortSearchIndices($currentSearchingIndices)
	{
		$sortedArray = array();
		foreach ($currentSearchingIndices as $indexName)
		{
			list($indexKey, $indexDate) = explode('-', $indexName, 2);
			$indexDate = intval($indexDate);
			$sortedArray[$indexDate] = $indexName;
		}
		krsort($sortedArray);
		return $sortedArray;
	}

	protected function runRotate()
	{
		$aliasesToRemove = array();
		$aliasesToAdd = array();

		list($currentIndexingIndices, $currentSearchingIndices) = $this->getCurrentStateMap();

		//remove old index aliases
		foreach ($currentIndexingIndices as $indexName)
		{
			$aliasesToRemove[] = new ElasticIndexAlias($indexName, $this->indexAlias);
		}

		$this->handleCurrentSearchIndices($currentSearchingIndices, $aliasesToRemove, $aliasesToAdd);

		//add latest to aliases
		$now = new DateTime();
		$yearMonth = $now->format($this->indexDateFormat);
		$newIndex = $this->indexPattern . '-' . $yearMonth;
		if (!$this->dryRun)
		{
			$this->createNewIndex($newIndex);
		}
		else
		{
			VidiunLog::debug("Dry run - creating index $newIndex");
		}

		$aliasesToAdd[] = new ElasticIndexAlias($newIndex, $this->searchAlias);
		$aliasesToAdd[] = new ElasticIndexAlias($newIndex, $this->indexAlias);

		$this->changeAliases($aliasesToAdd, $aliasesToRemove);
	}

	/**
	 * @param $currentSearchingIndices array
	 * @param $aliasesToRemove array
	 * @param $aliasesToAdd array
	 */
	protected function handleCurrentSearchIndices($currentSearchingIndices, &$aliasesToRemove, &$aliasesToAdd)
	{
		//remove old search aliases
		//keep only $maxNumberOfIndices indices with search alias
		$count = 0;
		foreach ($currentSearchingIndices as $index)
		{
			$count++;
			if ($count >= $this->maxNumberOfIndices)
			{
				$aliasesToRemove[] = new ElasticIndexAlias($index, $this->searchAlias);
			}
		}
	}

	protected function createNewIndex($indexName)
	{
		VidiunLog::log("Going to create index $indexName");
		try
		{
			$json = file_get_contents(ROOT_DIR . '/' . $this->mappingPath);
			$body = json_decode($json);
			$response = $this->client->createIndex($indexName, $body);
		}
		catch (Exception $e)
		{
			die("Failed to create new index $indexName\n");
		}
	}

}
