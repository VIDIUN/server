<?php
/**
 * @service eSearch
 * @package plugins.elasticSearch
 * @subpackage api.services
 */
class ESearchService extends VidiunBaseService
{
	/**
	 *
	 * @action searchEntry
	 * @param VidiunESearchEntryParams $searchParams
	 * @param VidiunPager $pager
	 * @return VidiunESearchEntryResponse
	 */
	function searchEntryAction(VidiunESearchEntryParams $searchParams, VidiunPager $pager = null)
	{
		$entrySearch = new vEntrySearch();
		list($coreResults, $objectCount) = $this->initAndSearch($entrySearch, $searchParams, $pager);
		$response = new VidiunESearchEntryResponse();
		$response->objects = VidiunESearchEntryResultArray::fromDbArray($coreResults, $this->getResponseProfile());
		$response->totalCount = $objectCount;
		return $response;
	}

	/**
	 *
	 * @action searchCategory
	 * @param VidiunESearchCategoryParams $searchParams
	 * @param VidiunPager $pager
	 * @return VidiunESearchCategoryResponse
	 */
	function searchCategoryAction(VidiunESearchCategoryParams $searchParams, VidiunPager $pager = null)
	{
		$categorySearch = new vCategorySearch();
		list($coreResults, $objectCount) = $this->initAndSearch($categorySearch, $searchParams, $pager);
		$response = new VidiunESearchCategoryResponse();
		$response->objects = VidiunESearchCategoryResultArray::fromDbArray($coreResults, $this->getResponseProfile());
		$response->totalCount = $objectCount;
		return $response;
	}

	/**
	 *
	 * @action searchUser
	 * @param VidiunESearchUserParams $searchParams
	 * @param VidiunPager $pager
	 * @return VidiunESearchUserResponse
	 */
	function searchUserAction(VidiunESearchUserParams $searchParams, VidiunPager $pager = null)
	{
		$userSearch = new vUserSearch();
		list($coreResults, $objectCount) = $this->initAndSearch($userSearch, $searchParams, $pager);
		$response = new VidiunESearchUserResponse();
		$response->objects = VidiunESearchUserResultArray::fromDbArray($coreResults, $this->getResponseProfile());
		$response->totalCount = $objectCount;
		return $response;
	}
	
	/**
	 * Creates a batch job that sends an email with a link to download a CSV containing a list of entries
	 *
	 * @action entryExportToCsv
	 * @actionAlias media.exportToCsv
	 * @param VidiunMediaEsearchExportToCsvJobData $data job data indicating filter to pass to the job
	 * @return string
	 *
	 * @throws APIErrors::USER_EMAIL_NOT_FOUND
	 */
	public function entryExportToCsvAction (VidiunMediaEsearchExportToCsvJobData $data)
	{
		if(!$data->userName || !$data->userMail)
			throw new VidiunAPIException(APIErrors::USER_EMAIL_NOT_FOUND, $vuser);
		
		$vJobdData = $data->toObject(new vMediaEsearchExportToCsvJobData());
		
		vJobsManager::addExportCsvJob($vJobdData, $this->getPartnerId(), ElasticSearchPlugin::getExportTypeCoreValue(EsearchMediaEntryExportObjectType::ESEARCH_MEDIA));
		
		return $data->userMail;
	}

	/**
	 * @param vBaseSearch $coreSearchObject
	 * @param $searchParams
	 * @param $pager
	 * @return array
	 */
	protected function initAndSearch($coreSearchObject, $searchParams, $pager)
	{
		list($coreSearchOperator, $objectStatusesArr, $objectId, $vPager, $coreOrder) =
			self::initSearchActionParams($searchParams, $pager);
		$elasticResults = $coreSearchObject->doSearch($coreSearchOperator, $vPager, $objectStatusesArr, $objectId, $coreOrder);

		list($coreResults, $objectCount) = vESearchCoreAdapter::transformElasticToCoreObject($elasticResults, $coreSearchObject);
		return array($coreResults, $objectCount);
	}

	protected static function initSearchActionParams($searchParams, VidiunPager $pager = null)
	{
		/**
		 * @var ESearchParams $coreParams
		 */
		$coreParams = $searchParams->toObject();

		$objectStatusesArr = array();
		$objectStatuses = $coreParams->getObjectStatuses();
		if (!empty($objectStatuses))
		{
			$objectStatusesArr = explode(',', $objectStatuses);
		}

		$vPager = null;
		if ($pager)
		{
			$vPager = $pager->toObject();
		}

		return array($coreParams->getSearchOperator(), $objectStatusesArr, $coreParams->getObjectId(), $vPager, $coreParams->getOrderBy());
	}

}